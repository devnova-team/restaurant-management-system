<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymobService;
use App\Services\Payment\PaypalService;
use App\Services\Payment\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    /**
     * Factory method to resolve the correct payment gateway service.
     */
    protected function getGatewayService(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'stripe' => new StripeService,
            'paypal' => new PaypalService,
            'paymob' => new PaymobService,
            default => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_gateway' => 'required|in:stripe,paypal,paymob',
        ]);

        $invoice = Invoice::with('order.items.menuItem')->find($id);

        if (! $invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        if ($invoice->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Invoice is already paid'], 400);
        }

        $gateway = $request->payment_gateway;

        $invoice->payment_method = $gateway;
        $invoice->save();

        try {
            $service = $this->getGatewayService($gateway);
            $checkoutUrl = $service->processPayment($invoice);

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function handleSuccess(Request $request, string $gateway): JsonResponse
    {
        try {
            $service = $this->getGatewayService($gateway);
            $isPaid = $service->handleCallback($request);

            if ($isPaid) {
                return response()->json(['success' => true, 'message' => "Payment via {$gateway} successful"]);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'Payment failed or not verified'], 400);
    }

    public function handleCancel(string $gateway): JsonResponse
    {
        return response()->json(['success' => false, 'message' => "Payment via {$gateway} cancelled"]);
    }

    public function handleWebhook(Request $request, string $gateway): JsonResponse
    {
        try {
            $service = $this->getGatewayService($gateway);
            $service->handleWebhook($request);

            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
