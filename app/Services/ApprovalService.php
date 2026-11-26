<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function submit(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'pending_approval']);

            $adminIds = User::where('can_approve', true)->pluck('id');

            $invoice->approvals()->delete();
            foreach ($adminIds as $id) {
                $invoice->approvals()->create(['admin_id' => $id, 'status' => 'pending']);
            }
        });
    }

    public function approve(Invoice $invoice, User $admin, ?string $comment = null): void
    {
        DB::transaction(function () use ($invoice, $admin, $comment) {
            $invoice->approvals()
                ->where('admin_id', $admin->id)
                ->update(['status' => 'approved', 'comment' => $comment, 'acted_at' => now()]);

            if ($comment) {
                $invoice->comments()->create([
                    'user_id' => $admin->id, 'body' => $comment, 'is_issue' => false,
                ]);
            }

            if ($invoice->approvals()->where('status', '!=', 'approved')->doesntExist()) {
                $invoice->update(['status' => 'approved', 'approved_at' => now()]);
            }
        });
    }

    public function reject(Invoice $invoice, User $admin, string $comment): void
    {
        DB::transaction(function () use ($invoice, $admin, $comment) {
            $invoice->approvals()
                ->where('admin_id', $admin->id)
                ->update(['status' => 'rejected', 'comment' => $comment, 'acted_at' => now()]);

            $invoice->update(['status' => 'rejected']);

            $invoice->comments()->create([
                'user_id' => $admin->id, 'body' => $comment, 'is_issue' => true,
            ]);
        });
    }
}
