<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePermission;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected ApprovalService $approvalService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $invoices = Invoice::with('creator')
            ->visibleTo($user)
            ->latest()
            ->paginate(15);

        return view('invoices.index', [
            'invoices' => $invoices,
            'canCreate' => Gate::allows('create', Invoice::class),
            'canManagePermissions' => $user->isAdmin(),
            // 'employees' => $user->isAdmin()
            //     ? User::where('role', 'employee')->orderBy('name')->get()
            //     : collect(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Invoice::class);

        return view('invoices.create');
    }

    public function store(StoreInvoiceRequest $request)
    {
        Log::debug('in Store');
        Gate::authorize('create', Invoice::class);
        $invoice = $this->invoiceService->create($request->validated(), $request->user()->id);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created. Submit for approval when ready.');
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load('items', 'creator', 'approvals.admin', 'comments.user', 'permissions.user');

        $employeePermissions = collect();
        $employees = collect();

        if (auth()->user()->isAdmin()) {
            $employees = User::all();
            $employeePermissions = $invoice->permissions->keyBy('user_id');
        }

        return view('invoices.show', [
            'invoice' => $invoice,
            'canEdit' => Gate::allows('update', $invoice),
            'canSubmit' => Gate::allows('submit', $invoice),
            'canApprove' => Gate::allows('approve', $invoice) && $invoice->status === 'pending_approval',
            // 'canGenerate' => Gate::allows('generatePdf', $invoice),
            'employees' => $employees,
            'employeePermissions' => $employeePermissions,
        ]);
    }

    public function edit(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);
        $invoice->load('items');

        return view('invoices.edit', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $this->invoiceService->update($invoice, $request->validated(), auth()->user()->isAdmin());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated.');

    }

    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        $invoice->delete();

        return back()->with('success', 'Invoice deleted');
    }

    public function submit(Invoice $invoice)
    {
        Gate::authorize('submit', $invoice);

        $this->approvalService->submit($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice sent to all admins for approval.');
    }

    public function invoicePermissions(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'invoice_id' => 'required|exists:invoices,id',
            'can_view' => ['sometimes', 'boolean'],
            'can_edit' => ['sometimes', 'boolean'],
            'can_create' => ['sometimes', 'boolean'],
            'can_delete' => ['sometimes', 'boolean'],
            'can_approve' => ['sometimes', 'boolean'],
            'expiry_days' => ['nullable', 'integer', 'min:1'],
        ]);

        Log::debug($data);

        $targetInvoicePermission = InvoicePermission::where(['invoice_id' => $data['invoice_id'], 'user_id' => $data['user_id']])->first();
        $targetUser = User::findOrFail($data['user_id']);
        $targetInvoice = Invoice::select('id', 'created_at')->where('id', $data['invoice_id'])->first();

        $targetInvoicePermission->update([
            'can_approve' => $request->boolean('can_approve'),
            'can_edit' => $request->boolean('can_edit'),
            'can_delete' => $request->boolean('can_delete'),
            'can_view' => $request->boolean('can_view'),
            'can_create' => $request->boolean('can_create'),
            'expires_at' => $data['expiry_days'] ? $targetInvoice->created_at->copy()->addDays((int) $data['expiry_days']) : null,
        ]);

        return back()->with('success', 'Permissions saved for'.' '.$targetUser->name);

    }
}
