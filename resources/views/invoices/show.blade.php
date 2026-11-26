<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Invoice</p>
                    <h2 class="font-mono font-semibold text-xl text-gray-900 leading-tight">
                        {{ $invoice->invoice_number }}
                    </h2>
                </div>
                <span @class([
                    'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ring-1 ring-inset',
                    'bg-gray-100 text-gray-700 ring-gray-500/20' =>
                        $invoice->status === 'draft',
                    'bg-amber-50 text-amber-800 ring-amber-600/20' =>
                        $invoice->status === 'pending_approval',
                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => in_array(
                        $invoice->status,
                        ['approved', 'generated']),
                    'bg-rose-50 text-rose-700 ring-rose-600/20' =>
                        $invoice->status === 'rejected',
                ])>
                    <span @class([
                        'h-1.5 w-1.5 rounded-full',
                        'bg-gray-500' => $invoice->status === 'draft',
                        'bg-amber-500' => $invoice->status === 'pending_approval',
                        'bg-emerald-500' => in_array($invoice->status, ['approved', 'generated']),
                        'bg-rose-500' => $invoice->status === 'rejected',
                    ])></span>
                    {{ str_replace('_', ' ', $invoice->status) }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($canEdit)
                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        Edit
                    </a>
                @endif

                @if ($canSubmit && !$canApprove)
                    <form method="POST" action="{{ route('invoices.submit', $invoice) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-400 text-gray-950 text-sm font-medium shadow-sm hover:bg-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 transition">
                            Submit for approval
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Overview & Customer Details --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Current Status</span>
                    <div class="text-sm font-semibold text-gray-900 capitalize mt-0.5">
                        {{ str_replace('_', ' ', $invoice->status) }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Grand Total</span>
                    <div class="text-xl font-bold text-gray-900 tabular-nums mt-0.5">
                        <span
                            class="text-xs font-medium text-gray-400 mr-1">{{ $invoice->currency }}</span>{{ number_format($invoice->total, 2) }}
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50/60 rounded-xl p-3.5 ring-1 ring-gray-100">
                    <div class="text-xs font-medium text-gray-500">Customer</div>
                    <div class="font-semibold text-gray-900 mt-0.5">{{ $invoice->customer_name }}</div>
                </div>

                <div class="bg-gray-50/60 rounded-xl p-3.5 ring-1 ring-gray-100">
                    <div class="text-xs font-medium text-gray-500">Email</div>
                    <div class="font-medium text-gray-900 mt-0.5">{{ $invoice->customer_email ?: '—' }}</div>
                </div>

                <div class="bg-gray-50/60 rounded-xl p-3.5 ring-1 ring-gray-100">
                    <div class="text-xs font-medium text-gray-500">Issue Date</div>
                    <div class="font-medium text-gray-900 tabular-nums mt-0.5">
                        {{ $invoice->issue_date->format('Y-m-d') }}
                    </div>
                </div>

                <div class="bg-gray-50/60 rounded-xl p-3.5 ring-1 ring-gray-100">
                    <div class="text-xs font-medium text-gray-500">Due Date</div>
                    <div class="font-medium text-gray-900 tabular-nums mt-0.5">
                        {{ $invoice->due_date?->format('Y-m-d') ?: '—' }}
                    </div>
                </div>

                <div class="sm:col-span-2 bg-gray-50/60 rounded-xl p-3.5 ring-1 ring-gray-100">
                    <div class="text-xs font-medium text-gray-500">Billing Address</div>
                    <div class="font-medium text-gray-900 mt-0.5">{{ $invoice->customer_address ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Line Items</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                    {{ $invoice->items->count() }}
                    {{ \Illuminate\Support\Str::plural('item', $invoice->items->count()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/40 text-xs uppercase tracking-wider text-gray-500">
                            <th class="py-3 px-6 font-semibold">Description</th>
                            <th class="py-3 px-4 font-semibold text-center">Qty</th>
                            <th class="py-3 px-4 font-semibold text-right">Unit price</th>
                            <th class="py-3 px-6 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($invoice->items as $item)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-6 font-medium text-gray-900">{{ $item->description }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 tabular-nums">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 text-right text-gray-700 tabular-nums">
                                    {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="py-3 px-6 text-right font-medium text-gray-900 tabular-nums">
                                    {{ number_format($item->total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200 bg-gray-50/40 text-sm">
                        <tr>
                            <td colspan="3" class="py-2.5 px-6 text-right text-gray-500">Subtotal</td>
                            <td class="py-2.5 px-6 text-right font-medium text-gray-900 tabular-nums">
                                {{ number_format($invoice->subtotal, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-2.5 px-6 text-right text-gray-500">Tax
                                ({{ $invoice->tax_rate }}%)</td>
                            <td class="py-2.5 px-6 text-right font-medium text-gray-900 tabular-nums">
                                {{ number_format($invoice->tax_amount, 2) }}
                            </td>
                        </tr>
                        <tr class="border-t border-gray-200 bg-gray-50/80 font-bold text-gray-900">
                            <td colspan="3" class="py-3 px-6 text-right">Total</td>
                            <td class="py-3 px-6 text-right tabular-nums">
                                {{ number_format($invoice->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Admin Approvals --}}
        @if ($invoice->approvals->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-base font-semibold text-gray-900">Admin Approvals</h3>
                </div>

                <ul class="divide-y divide-gray-100 px-6">
                    @foreach ($invoice->approvals as $approval)
                        <li class="py-3.5 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-gray-900">{{ $approval->admin->name }}</span>
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ring-1 ring-inset',
                                    'bg-amber-50 text-amber-800 ring-amber-600/20' =>
                                        $approval->status === 'pending',
                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20' =>
                                        $approval->status === 'approved',
                                    'bg-rose-50 text-rose-700 ring-rose-600/20' =>
                                        $approval->status === 'rejected',
                                ])>
                                    {{ $approval->status }}
                                </span>
                            </div>
                            @if ($approval->comment)
                                <p class="mt-1.5 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                    ↳ {{ $approval->comment }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Approval / Rejection Decision --}}
        @if ($canApprove)
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-base font-semibold text-gray-900">Your Decision</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Approve this invoice or reject it with a required
                        explanation.</p>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <form method="POST" action="{{ route('invoices.approve', $invoice) }}"
                        class="rounded-xl border border-gray-200 p-4 bg-gray-50/40 flex flex-col justify-between space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1.5">Approve Invoice</label>
                            <textarea name="comment" rows="2" placeholder="Optional approval note..."
                                class="w-full rounded-xl border-gray-300 bg-white text-sm p-2.5 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"></textarea>
                        </div>
                        <div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
                                Approve
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('invoices.reject', $invoice) }}"
                        class="rounded-xl border border-rose-200/80 p-4 bg-rose-50/30 flex flex-col justify-between space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1.5">
                                Reject Invoice <span class="text-rose-600">*</span>
                            </label>
                            <textarea name="comment" rows="2" required placeholder="Describe the issue (required)..."
                                class="w-full rounded-xl border-gray-300 bg-white text-sm p-2.5 shadow-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20"></textarea>
                        </div>
                        <div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-medium shadow-sm hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition">
                                Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Admin Permissions Dropdown + Matrix --}}
        @if (auth()->user()->isAdmin())
            @php
                $selectedPermUserId = old('user_id');
                $permissionColumns = [
                    'can_create' => ['label' => 'Create', 'dataset' => 'canCreate'],
                    'can_view' => ['label' => 'View', 'dataset' => 'canView'],
                    'can_edit' => ['label' => 'Update', 'dataset' => 'canEdit'],
                    'can_delete' => ['label' => 'Delete', 'dataset' => 'canDelete'],
                    'can_approve' => ['label' => 'Approve', 'dataset' => 'canApprove'],
                ];
            @endphp

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-base font-semibold text-gray-900">User Permissions for this Invoice</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Select a user to load their current permissions. Enter a number of days to add to the invoice
                        creation date (leave blank for no day offset).
                    </p>
                </div>

                <form method="POST" action="{{ route('invoices.permissions') }}" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {{-- User Dropdown --}}
                        <div class="lg:col-span-4">
                            <label for="invoice-user-select" class="block text-sm font-medium text-gray-700 mb-1.5">
                                User <span class="text-rose-500">*</span>
                            </label>
                            <select name="user_id" id="invoice-user-select" required
                                class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                <option value="" disabled {{ $selectedPermUserId ? '' : 'selected' }}>— Select a
                                    user —</option>
                                @foreach ($employees as $emp)
                                    @php
                                        $perm = $employeePermissions[$emp->id] ?? null;
                                    @endphp
                                    <option value="{{ $emp->id }}"
                                        data-can-create="{{ $perm?->can_create ?? $emp->can_create ? '1' : '0' }}"
                                        data-can-view="{{ $perm?->can_view ?? $emp->can_view ? '1' : '0' }}"
                                        data-can-edit="{{ $perm?->can_edit ?? $emp->can_edit ? '1' : '0' }}"
                                        data-can-delete="{{ $perm?->can_delete ?? $emp->can_delete ? '1' : '0' }}"
                                        data-can-approve="{{ $perm?->can_approve ?? $emp->can_approve ? '1' : '0' }}"
                                        data-expiry-days="{{ $perm?->expiry_days ?? '' }}"
                                        {{ (string) $selectedPermUserId === (string) $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Permission Checkboxes --}}
                        <div class="lg:col-span-5">
                            <span class="block text-sm font-medium text-gray-700 mb-1.5">Permissions</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach ($permissionColumns as $field => $meta)
                                    <label
                                        class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-gray-50/40 hover:bg-indigo-50/40 hover:border-indigo-200 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400 cursor-pointer transition text-sm">
                                        <input type="checkbox" name="{{ $field }}" value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-gray-900">{{ $meta['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Number Input for Days Added to Invoice Creation Date --}}
                        <div class="lg:col-span-3">
                            <label for="expiry-days-input" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Days after creation date
                            </label>
                            <input type="number" name="expiry_days" id="expiry-days-input" min="0"
                                step="1" value="{{ old('expiry_days') }}" placeholder="e.g. 3"
                                class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 tabular-nums shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            <p class="text-[11px] text-gray-500 mt-1">
                                Invoice created: <span
                                    class="font-mono tabular-nums">{{ $invoice->created_at->format('Y-m-d') }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            Save permissions
                        </button>
                    </div>
                </form>

                {{-- Users Permissions Table with Tick / Cross --}}
                <div class="border-t border-gray-100 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500">
                                <th class="py-3 px-6 font-semibold">User</th>
                                <th class="py-3 px-3 font-semibold text-center">Create</th>
                                <th class="py-3 px-3 font-semibold text-center">View</th>
                                <th class="py-3 px-3 font-semibold text-center">Update</th>
                                <th class="py-3 px-3 font-semibold text-center">Delete</th>
                                <th class="py-3 px-3 font-semibold text-center">Approve</th>
                                <th class="py-3 px-6 font-semibold text-right">Permission Till</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $emp)
                                @php
                                    $perm = $employeePermissions[$emp->id] ?? null;
                                    $rowFlags = [
                                        (bool) ($perm?->can_create ?? $emp->can_create),
                                        (bool) ($perm?->can_view ?? $emp->can_view),
                                        (bool) ($perm?->can_edit ?? $emp->can_edit),
                                        (bool) ($perm?->can_delete ?? $emp->can_delete),
                                        (bool) ($perm?->can_approve ?? $emp->can_approve),
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-3 px-6">
                                        <div class="font-semibold text-gray-900">{{ $emp->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $emp->email }}</div>
                                    </td>

                                    @foreach ($rowFlags as $allowed)
                                        <td class="py-3 px-3 text-center">
                                            @if ($allowed)
                                                <span
                                                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-emerald-50 text-emerald-700 font-bold ring-1 ring-inset ring-emerald-600/20">
                                                    ✓
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-rose-50 text-rose-600 font-bold ring-1 ring-inset ring-rose-600/20">
                                                    ✕
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="py-3 px-6 text-right tabular-nums text-xs font-medium text-gray-700">
                                        @if (!is_null($perm?->expires_at))
                                            {{ $perm->expires_at->format('d-m-Y') }}
                                            {{-- <span class="text-gray-400">
                                                ({{ $invoice->created_at->copy()->addDays((int) $perm->expiry_days)->format('Y-m-d') }})
                                            </span> --}}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-gray-500">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Comments / Issues --}}
        @if ($invoice->comments->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Comments & Issues</h3>
                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                        {{ $invoice->comments->count() }}
                    </span>
                </div>

                <ul class="divide-y divide-gray-100 p-6 space-y-3">
                    @foreach ($invoice->comments as $comment)
                        <li @class([
                            'pt-3 first:pt-0 rounded-xl p-3.5 border-l-4',
                            'border-rose-500 bg-rose-50/60' => $comment->is_issue,
                            'border-gray-300 bg-gray-50/50' => !$comment->is_issue,
                        ])>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-1">
                                <span class="font-semibold text-gray-800">{{ $comment->user->name }}</span>
                                <span>•</span>
                                <span>{{ $comment->created_at->diffForHumans() }}</span>
                                @if ($comment->is_issue)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">
                                        ISSUE
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-800">{{ $comment->body }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('invoice-user-select');
                const daysInput = document.getElementById('expiry-days-input');
                if (!select) return;

                const flags = [
                    ['can_create', 'canCreate'],
                    ['can_view', 'canView'],
                    ['can_edit', 'canEdit'],
                    ['can_delete', 'canDelete'],
                    ['can_approve', 'canApprove'],
                ];

                function syncPermissionsForm() {
                    const option = select.options[select.selectedIndex];
                    const hasUser = Boolean(option && option.value);

                    flags.forEach(([inputName, datasetKey]) => {
                        const checkbox = document.querySelector(`input[name="${inputName}"]`);
                        if (!checkbox) return;
                        checkbox.checked = hasUser && option.dataset[datasetKey] === '1';
                    });

                    if (daysInput) {
                        daysInput.value = hasUser ? (option.dataset.expiryDays ?? '') : '';
                    }
                }

                select.addEventListener('change', syncPermissionsForm);
                syncPermissionsForm();
            });
        </script>
    @endpush
</x-app-layout>
