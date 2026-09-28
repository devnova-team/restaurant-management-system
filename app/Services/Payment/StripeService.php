<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService implements PaymentGatewayInterface
{
    public function processPayment(Invoice $invoice): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Order #'.$invoice->order_id,
                    ],
                    'unit_amount' => (int) ($invoice->total_amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payments.success', ['gateway' => 'stripe']).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payments.cancel', ['gateway' => 'stripe']),
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
        ]);

        $invoice->transaction_id = $session->id;
        $invoice->save();

        return $session->url;
    }

    public function handleCallback(Request $request): bool
    {
        $sessionId = $request->get('session_id');
        if (! $sessionId) {
            return false;
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            $invoiceId = $session->metadata->invoice_id;
            $invoice = Invoice::find($invoiceId);

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
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return false;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return false;
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status === 'paid') {
                $invoiceId = $session->metadata->invoice_id ?? null;
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
}
