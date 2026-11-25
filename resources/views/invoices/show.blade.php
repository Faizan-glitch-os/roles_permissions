<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ $invoice->invoice_number }}</h2>
            <div class="flex gap-2">
                @if ($canEdit)
                    <a href="{{ route('invoices.edit', $invoice) }}" class="px-3 py-1 border rounded">Edit</a>
                @endif
                @if ($canSubmit && !$canApprove)
                    <form method="POST" action="{{ route('invoices.submit', $invoice) }}">
                        @csrf
                        <button class="px-3 py-1 bg-yellow-400 text-black rounded">Submit for approval</button>
                    </form>
                @endif
                {{-- @if ($canGenerate)
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="px-3 py-1 bg-green-600 text-white rounded">
                        {{ $invoice->status === 'generated' ? 'Download PDF' : 'Generate PDF' }}
                    </a>
                @endif --}}
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 space-y-6">

        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between mb-4">
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="font-semibold capitalize">{{ str_replace('_', ' ', $invoice->status) }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="font-bold text-lg">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Customer:</span> {{ $invoice->customer_name }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $invoice->customer_email ?: '—' }}</div>
                <div><span class="text-gray-500">Issue date:</span> {{ $invoice->issue_date->format('Y-m-d') }}</div>
                <div><span class="text-gray-500">Due date:</span> {{ $invoice->due_date?->format('Y-m-d') ?: '—' }}
                </div>
                <div class="col-span-2"><span class="text-gray-500">Address:</span>
                    {{ $invoice->customer_address ?: '—' }}</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold mb-3">Items</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Description</th>
                        <th class="p-2">Qty</th>
                        <th class="p-2">Unit price</th>
                        <th class="p-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr class="border-t">
                            <td class="p-2">{{ $item->description }}</td>
                            <td class="p-2 text-center">{{ $item->quantity }}</td>
                            <td class="p-2 text-center">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="p-2 text-right">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="text-sm">
                    <tr>
                        <td colspan="3" class="text-right p-2">Subtotal</td>
                        <td class="text-right p-2">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right p-2">Tax ({{ $invoice->tax_rate }}%)</td>
                        <td class="text-right p-2">{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td colspan="3" class="text-right p-2">Total</td>
                        <td class="text-right p-2">{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($invoice->approvals->isNotEmpty())
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold mb-3">Admin Approvals</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($invoice->approvals as $approval)
                        <li class="flex justify-between items-center border-b pb-1">
                            <span>{{ $approval->admin->name }}</span>
                            <span
                                class="px-2 py-0.5 rounded text-xs
                            @class([
                                'bg-yellow-200 text-yellow-800' => $approval->status === 'pending',
                                'bg-green-200 text-green-800' => $approval->status === 'approved',
                                'bg-red-200 text-red-800' => $approval->status === 'rejected',
                            ])">{{ $approval->status }}</span>
                        </li>
                        @if ($approval->comment)
                            <li class="text-xs text-gray-600 ml-2">↳ {{ $approval->comment }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($canApprove)
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold mb-3">Your Decision</h3>
                <form method="POST" action="{{ route('invoices.approve', $invoice) }}" class="mb-3">
                    @csrf
                    <textarea name="comment" rows="2" placeholder="Optional comment" class="w-full border rounded p-2 mb-2"></textarea>
                    <button class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
                </form>
                <form method="POST" action="{{ route('invoices.reject', $invoice) }}">
                    @csrf
                    <textarea name="comment" rows="2" required placeholder="Describe the issue (required)"
                        class="w-full border rounded p-2 mb-2"></textarea>
                    <button class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
                </form>
            </div>
        @endif
        @if (auth()->user()->isAdmin())
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold mb-3">Employee Permissions (this invoice)</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Grant per-invoice view/edit access. Leave "Forever" for permanent access,
                    or pick a date to auto-revoke.
                </p>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left bg-gray-50">
                            <th class="p-2">Employee</th>
                            <th class="p-2">Current</th>
                            <th class="p-2">Grant / Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $emp)
                            @php $perm = $employeePermissions[$emp->id] ?? null; @endphp
                            <tr class="border-t align-top">
                                <td class="p-2">
                                    <div class="font-medium">{{ $emp->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $emp->email }}</div>
                                </td>
                                <td class="p-2 text-xs">
                                    @if ($perm)
                                        <div>View: {{ $perm->can_view ? '✓' : '—' }}</div>
                                        <div>Edit: {{ $perm->can_edit ? '✓' : '—' }}</div>
                                        <div>
                                            Expires:
                                            @if ($perm->expires_at)
                                                {{ $perm->expires_at->format('Y-m-d H:i') }}
                                                @if ($perm->expires_at->isPast())
                                                    <span class="text-red-600">(expired)</span>
                                                @endif
                                            @else
                                                never
                                            @endif
                                        </div>
                                        <form method="POST"
                                            action="{{ route('admin.invoices.permissions.revoke', [$invoice, $emp]) }}"
                                            onsubmit="return confirm('Revoke all permissions for {{ $emp->name }} on this invoice?')"
                                            class="mt-1">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 text-xs">Revoke</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">No permission</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <form method="POST" action="{{ route('admin.permissions.store') }}"
                                        class="grid grid-cols-2 gap-2">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $emp->id }}">
                                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                                        <label class="text-xs flex items-center gap-1">
                                            <input type="checkbox" name="can_view" value="1"
                                                @checked($perm?->can_view)> View
                                        </label>
                                        <label class="text-xs flex items-center gap-1">
                                            <input type="checkbox" name="can_edit" value="1"
                                                @checked($perm?->can_edit)> Edit
                                        </label>

                                        <label class="text-xs flex items-center gap-1">
                                            <input type="radio" name="expiry_mode" value="forever"
                                                @checked(!$perm?->expires_at)
                                                onchange="this.closest('form').querySelector('[name=expires_at]').disabled = true">
                                            Forever
                                        </label>
                                        <label class="text-xs flex items-center gap-1">
                                            <input type="radio" name="expiry_mode" value="date"
                                                @checked($perm?->expires_at)
                                                onchange="this.closest('form').querySelector('[name=expires_at]').disabled = false">
                                            Until
                                        </label>

                                        <input type="datetime-local" name="expires_at"
                                            value="{{ $perm?->expires_at?->format('Y-m-d\TH:i') }}"
                                            class="col-span-2 border rounded p-1 text-xs"
                                            @if (!$perm?->expires_at) disabled @endif>

                                        <button class="col-span-2 px-2 py-1 bg-indigo-600 text-white rounded text-xs">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if ($invoice->comments->isNotEmpty())
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold mb-3">Comments / Issues</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($invoice->comments as $comment)
                        <li
                            class="border-l-4 pl-3 {{ $comment->is_issue ? 'border-red-500 bg-red-50' : 'border-gray-300' }} p-2">
                            <div class="text-xs text-gray-500">
                                {{ $comment->user->name }} • {{ $comment->created_at->diffForHumans() }}
                                @if ($comment->is_issue)
                                    • <span class="text-red-600 font-semibold">ISSUE</span>
                                @endif
                            </div>
                            <div>{{ $comment->body }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
