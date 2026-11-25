<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            'employees' => $user->isAdmin()
                ? User::where('role', 'employee')->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Invoice::class);

        return view('invoices.create');
    }

    public function store(StoreInvoiceRequest $request)
    {
        Gate::authorize('create', Invoice::class);

        $invoice = $this->invoiceService->create($request->validated(), $request->user()->id);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created. Submit for approval when ready.');
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load('items', 'creator', 'approvals.admin', 'comments.user', 'permissions.user');

        $employeePermissions = collect();
        $employees = collect();

        if (auth()->user()->isAdmin()) {
            $employees = User::where('role', 'employee')->orderBy('name')->get();
            $employeePermissions = $invoice->permissions->keyBy('user_id');
        }

        return view('invoices.show', [
            'invoice' => $invoice,
            'canEdit' => Gate::allows('update', $invoice),
            'canSubmit' => Gate::allows('submit', $invoice),
            'canApprove' => auth()->user()->isAdmin()
                            && $invoice->status === 'pending_approval',
            'canGenerate' => Gate::allows('generatePdf', $invoice),
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

    public function submit(Invoice $invoice)
    {
        Gate::authorize('submit', $invoice);

        $this->approvalService->submit($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice sent to all admins for approval.');
    }
}
