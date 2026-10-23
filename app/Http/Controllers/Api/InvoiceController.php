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
            $request->validated(),
            $request->user()
        );

        return ApiResponse::success(
            $invoice,
            'تم إنشاء الفاتورة بنجاح.',
            201
        );
    }

    public function show(int $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($invoice, request()->user());

        if (! $invoice) {
            return ApiResponse::error(
                'الفاتورة غير موجودة.',
                404
            );
        }

        return ApiResponse::success(
            $invoice,
            'تم جلب الفاتورة بنجاح.'
        );
    }

    public function pay(PayInvoiceRequest $request, int $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->payInvoice(
            $invoice,
            (float) $request->validated('amount'),
            $request->user()
        );

        return ApiResponse::success(
            $invoice,
            'تم سداد الفاتورة بنجاح.'
        );
    }

    public function storePayment(PayInvoiceRequest $request, int $invoice): JsonResponse
    {
        return $this->pay($request, $invoice);
    }
}
