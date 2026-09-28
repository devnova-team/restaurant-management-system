<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Process the payment and return the checkout URL.
     */
    public function processPayment(Invoice $invoice): string;

    /**
     * Handle the callback from the payment gateway and verify payment.
     * Returns true if payment was successful, false otherwise.
     */
    public function handleCallback(Request $request): bool;

    /**
     * Handle webhook request from the payment gateway asynchronously.
     * Returns true if successfully verified and processed.
     */
    public function handleWebhook(Request $request): bool;
}
