<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicePermission;
use App\Models\User;
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
            $this->copyPermissions($invoice, $userId);

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

    protected function copyPermissions($invoice, $granter_id)
    {

        $userPermissions = User::query()
            ->get(['id', 'can_approve', 'can_view', 'can_edit', 'can_delete', 'can_create'])
            ->map(function ($u) use ($invoice, $granter_id) {
                return [
                    'invoice_id' => $invoice->id,
                    'user_id' => $u->id,
                    'granted_by' => $granter_id,
                    'can_approve' => $u->can_approve,
                    'can_view' => $u->can_view,
                    'can_edit' => $u->can_edit,
                    'can_create' => $u->can_create,
                    'can_delete' => $u->can_delete,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        if ($userPermissions) {
            InvoicePermission::insert($userPermissions);
        }
    }
}
