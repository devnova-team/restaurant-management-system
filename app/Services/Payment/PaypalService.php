<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalService implements PaymentGatewayInterface
{
    public function processPayment(Invoice $invoice): string
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $order = $provider->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => (string) $invoice->id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($invoice->total_amount, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'cancel_url' => route('payments.cancel', ['gateway' => 'paypal']),
                'return_url' => route('payments.success', ['gateway' => 'paypal']),
            ],
        ]);

        if (isset($order['id']) && $order['id'] != null) {
            $invoice->transaction_id = $order['id'];
            $invoice->save();

            foreach ($order['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return $links['href'];
                }
            }
        }

        throw new Exception('Something went wrong with PayPal');
    }

    public function handleCallback(Request $request): bool
    {
        if (! $request->token) {
            return false;
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->token);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $invoiceId = $response['purchase_units'][0]['reference_id'] ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $invoice->payment_status = 'paid';
                    $invoice->save();

                    return true;
                }
            }
        }

        return false;
    }

    public function handleWebhook(Request $request): bool
    {
        // This is a simplified webhook handler for the payment capture completed event.
        // For production, you MUST verify the webhook signature using PayPal's SDK/API.
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED' && isset($resource['supplementary_data']['related_ids']['order_id'])) {
            $payPalOrderId = $resource['supplementary_data']['related_ids']['order_id'];

            $invoice = Invoice::where('transaction_id', $payPalOrderId)->first();

            if ($invoice) {
                $invoice->payment_status = 'paid';
                $invoice->save();

                return true;
            }
        }

        return false;
    }
}
