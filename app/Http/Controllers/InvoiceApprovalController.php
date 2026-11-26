<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceApprovalController extends Controller
{
    public function __construct(protected ApprovalService $approvalService) {}

    public function approve(Request $request, Invoice $invoice)
    {
        Gate::authorize('approve', $invoice);

        $data = $request->validate(['comment' => 'nullable|string|max:2000']);

        $this->approvalService->approve($invoice, $request->user(), $data['comment'] ?? null);

        return back()->with('success', 'Invoice approved.');
    }

    public function reject(Request $request, Invoice $invoice)
    {
        Gate::authorize('approve', $invoice);

        $data = $request->validate(['comment' => 'required|string|max:2000']);

        $this->approvalService->reject($invoice, $request->user(), $data['comment']);

        return back()->with('success', 'Invoice rejected.');
    }
}
