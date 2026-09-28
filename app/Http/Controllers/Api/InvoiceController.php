<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Interfaces\InvoiceInterface;
use App\Models\Order;
use App\Services\BillingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected InvoiceInterface $invoiceRepository
    ) {}

    public function store(
        StoreInvoiceRequest $request
    ): JsonResponse {

        try {
            $order = Order::find(
                $request->order_id
            );

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            $invoice = $this->billingService->createInvoice(
                $order,
                $request->payment_method
            );

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data' => $invoice,
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->findById($id);

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    public function updatePaymentStatus(
        UpdatePaymentStatusRequest $request,
        int $id
    ): JsonResponse {

        $invoice = $this->invoiceRepository->findById($id);

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->billingService
            ->updatePaymentStatus(
                $invoice,
                $request->payment_status
            );

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully.',
            'data' => $invoice,
        ]);
    }
}
