<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::nextNumber(),
                'created_by' => $userId,
                'status' => 'draft',
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'tax_rate' => $data['tax_rate'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);

            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $data, $isAdmin): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $isAdmin) {
            $invoice->update([
                'status' => $isAdmin ? $invoice->status : 'pending_approval',
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'currency' => $data['currency'] ?? $invoice->currency,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);

            return $invoice;
        });
    }

    protected function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach ($items as $item) {
            if (empty($item['description'])) {
                continue;
            }
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($qty * $price, 2),
            ]);
        }
        $invoice->recalculateTotals();
    }
}
