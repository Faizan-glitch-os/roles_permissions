<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        // if ($invoice->created_by === $user->id) {
        //     return true;
        // }

        return $this->hasPermission($user, $invoice, 'can_view');
    }

    public function create(User $user): bool
    {
        // return $user->canCreateInvoice();
        return true;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($invoice->isLocked()) {
            return false;
        }

        // if ($invoice->status === 'pending_approval') {
        //     return false;
        // }

        if ($invoice->created_by === $user->id) {
            return true;
        }

        return $this->hasPermission($user, $invoice, 'can_edit');
    }

    public function submit(User $user, Invoice $invoice): bool
    {
        return $invoice->created_by === $user->id
            && in_array($invoice->status, ['draft', 'rejected']);
    }

    public function approve(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function generatePdf(User $user, Invoice $invoice): bool
    {
        if (! in_array($invoice->status, ['approved', 'generated'])) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($invoice->created_by === $user->id) {
            return true;
        }

        return $this->hasPermission($user, $invoice, 'can_view');
    }

    protected function hasPermission(User $user, Invoice $invoice, string $ability): bool
    {
        $column = $ability === 'can_view' ? 'can_view' : 'can_edit';

        return $user->permissions()
            ->where($column, true)
            ->where(function ($q) use ($invoice) {
                $q->where('invoice_id', $invoice->id)->orWhereNull('invoice_id');
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
