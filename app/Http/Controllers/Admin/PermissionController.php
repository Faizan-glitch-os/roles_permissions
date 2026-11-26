<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePermission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $users = User::all();
        $invoices = Invoice::orderByDesc('id')->limit(200)->get();

        $invoicePermissions = InvoicePermission::with('invoice')
            ->whereIn('user_id', $users->pluck('id'))
            ->latest()
            ->get()
            ->groupBy('user_id');

        return view('admin.permissions.index', compact('users', 'invoices', 'invoicePermissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'can_view' => 'boolean',
            'can_edit' => 'boolean',
            'can_create' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'expiry_mode' => 'nullable|in:forever,date',
        ]);

        $target = User::findOrFail($data['user_id']);
        abort_unless($target->isEmployee(), 422, 'Permissions apply only to employees.');

        $expiresAt = ($request->input('expiry_mode') === 'forever')
            ? null
            : ($data['expires_at'] ?? null);

        InvoicePermission::updateOrCreate(
            [
                'user_id' => $target->id,
                'invoice_id' => $data['invoice_id'] ?? null,
            ],
            [
                'can_view' => $request->boolean('can_view'),
                'can_edit' => $request->boolean('can_edit'),
                'can_create' => $request->boolean('can_create'),
                'expires_at' => $expiresAt,
                'granted_by' => $request->user()->id,
            ]
        );

        return back()->with('success', 'Permission saved for '.$target->name.'.');
    }

    public function revokeForInvoice(Request $request, Invoice $invoice, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        InvoicePermission::where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Permission revoked for '.$user->name.'.');
    }

    public function destroy(InvoicePermission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission revoked.');
    }
}
