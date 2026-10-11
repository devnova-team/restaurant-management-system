<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice(
            $request->validated()
        );

        return ApiResponse::success(
            $invoice,
            'Invoice created successfully.',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);

        if (! $invoice) {
            return ApiResponse::error(
                'Invoice not found.',
                404
            );
        }

        return ApiResponse::success(
            $invoice,
            'Invoice retrieved successfully.'
        );
    }

    public function pay(PayInvoiceRequest $request, int $id): JsonResponse
    {
        $invoice = $this->invoiceService->payInvoice(
            $id,
            (float) $request->validated('amount')
        );

        return ApiResponse::success(
            $invoice,
            'Invoice paid successfully.'
        );
    }
}
