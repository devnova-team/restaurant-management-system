<?php

namespace App\Interfaces;

use App\Models\Invoice;

interface InvoiceInterface
{
    public function create(array $data): Invoice;

    public function findById(int $id): ?Invoice;

    public function findByOrderId(int $orderId): ?Invoice;

    public function update(
        Invoice $invoice,
        array $data
    ): Invoice;
}
