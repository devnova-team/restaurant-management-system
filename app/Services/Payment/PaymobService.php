<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymobService implements PaymentGatewayInterface
{
    public function processPayment(Invoice $invoice): string
    {
        $amountCents = (int) ($invoice->total_amount * 100);

        // 1. Authentication
        $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => config('services.paymob.api_key'),
        ]);

        if (! $authResponse->successful()) {
            throw new Exception('Paymob Authentication failed');
        }
        $authToken = $authResponse->json('token');

        // 2. Order Registration
        $orderResponse = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
            'auth_token' => $authToken,
            'delivery_needed' => 'false',
            'amount_cents' => $amountCents,
            'currency' => 'EGP', // Typically EGP for Paymob
            'items' => [],
        ]);

        if (! $orderResponse->successful()) {
            throw new Exception('Paymob Order Registration failed');
        }
        $paymobOrderId = $orderResponse->json('id');

        // 3. Payment Key Generation
        $paymentKeyResponse = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'auth_token' => $authToken,
            'amount_cents' => $amountCents,
            'expiration' => 3600,
            'order_id' => $paymobOrderId,
            'billing_data' => [
                'apartment' => 'NA',
                'email' => 'customer@example.com',
                'floor' => 'NA',
                'first_name' => 'Customer',
                'street' => 'NA',
                'building' => 'NA',
                'phone_number' => '01000000000',
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city' => 'NA',
                'country' => 'EG',
                'last_name' => 'Name',
                'state' => 'NA',
            ],
            'currency' => 'EGP',
            'integration_id' => config('services.paymob.integration_id'),
        ]);

        if (! $paymentKeyResponse->successful()) {
            throw new Exception('Paymob Payment Key Generation failed');
        }
        $paymentToken = $paymentKeyResponse->json('token');

        $invoice->transaction_id = $paymobOrderId;
        $invoice->save();

        $iframeId = config('services.paymob.iframe_id');

        return "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
    }

    public function handleCallback(Request $request): bool
    {
        $data = $request->all();

        $success = $data['success'] ?? 'false';
        $paymobOrderId = $data['order'] ?? null;

        if ($success === 'true' && $paymobOrderId) {
            $invoice = Invoice::where('transaction_id', $paymobOrderId)->first();

            if ($invoice) {
                $invoice->payment_status = 'paid';
                $invoice->save();

                return true;
            }
        }

        return false;
    }

    public function handleWebhook(Request $request): bool
    {
        $data = $request->all();
        $hmacString = $request->query('hmac'); // Paymob sometimes sends it in query or header

        $obj = $data['obj'] ?? [];

        $amount_cents = $obj['amount_cents'] ?? '';
        $created_at = $obj['created_at'] ?? '';
        $currency = $obj['currency'] ?? '';
        $error_occured = ($obj['error_occured'] ?? false) ? 'true' : 'false';
        $has_parent_transaction = ($obj['has_parent_transaction'] ?? false) ? 'true' : 'false';
        $id = $obj['id'] ?? '';
        $integration_id = $obj['integration_id'] ?? '';
        $is_3d_secure = ($obj['is_3d_secure'] ?? false) ? 'true' : 'false';
        $is_auth = ($obj['is_auth'] ?? false) ? 'true' : 'false';
        $is_capture = ($obj['is_capture'] ?? false) ? 'true' : 'false';
        $is_refunded = ($obj['is_refunded'] ?? false) ? 'true' : 'false';
        $is_standalone_payment = ($obj['is_standalone_payment'] ?? false) ? 'true' : 'false';
        $is_voided = ($obj['is_voided'] ?? false) ? 'true' : 'false';
        $order_id = $obj['order']['id'] ?? '';
        $owner = $obj['owner'] ?? '';
        $pending = ($obj['pending'] ?? false) ? 'true' : 'false';
        $source_data_pan = $obj['source_data']['pan'] ?? '';
        $source_data_sub_type = $obj['source_data']['sub_type'] ?? '';
        $source_data_type = $obj['source_data']['type'] ?? '';
        $success = ($obj['success'] ?? false) ? 'true' : 'false';

        $concatenatedString = $amount_cents.$created_at.$currency.$error_occured.
                              $has_parent_transaction.$id.$integration_id.$is_3d_secure.
                              $is_auth.$is_capture.$is_refunded.$is_standalone_payment.
                              $is_voided.$order_id.$owner.$pending.$source_data_pan.
                              $source_data_sub_type.$source_data_type.$success;

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, config('services.paymob.hmac'));

        if ($calculatedHmac === $hmacString) {
            if ($success === 'true') {
                $invoice = Invoice::where('transaction_id', $order_id)->first();
                if ($invoice) {
                    $invoice->payment_status = 'paid';
                    $invoice->save();

                    return true;
                }
            }
        }

        return false;
    }
}
