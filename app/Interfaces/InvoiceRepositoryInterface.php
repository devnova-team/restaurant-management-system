<?php

namespace App\Interfaces;

use App\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function create(array $data): Invoice;

    public function findById(int $id): ?Invoice;

    public function findByOrderId(int $orderId): ?Invoice;

    public function updatePaymentStatus(
        Invoice $invoice,
        string $status
    ): Invoice;
}
