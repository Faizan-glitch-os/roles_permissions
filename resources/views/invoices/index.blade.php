<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Invoices
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Track billing status, totals, and approval progress.
                </p>
            </div>

            @if ($canManagePermissions)
                <a href="{{ route('admin.permissions.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-orange-400 text-white text-sm font-medium shadow-sm hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <span>+</span>
                    <span>Manage Permissions</span>
                </a>
            @endif

            @if ($canCreate)
                <a href="{{ route('invoices.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <span>+</span>
                    <span>New Invoice</span>
                </a>
            @else
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                    Read-only access — cannot create invoices
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">All Invoices</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                    {{ method_exists($invoices, 'total') ? $invoices->total() : count($invoices) }}
                    {{ \Illuminate\Support\Str::plural('invoice', method_exists($invoices, 'total') ? $invoices->total() : count($invoices)) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/40 text-xs uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-6 font-semibold">#</th>
                            <th class="py-3.5 px-4 font-semibold">Customer</th>
                            <th class="py-3.5 px-4 font-semibold">Created by</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Total</th>
                            <th class="py-3.5 px-4 font-semibold">Date</th>
                            <th class="py-3.5 px-6 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                            @php
                                $creatorName = $invoice->creator->name ?? 'System';
                                $initials = collect(explode(' ', trim($creatorName)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');
                                $statusLabel = str_replace('_', ' ', $invoice->status);
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3.5 px-6 font-mono text-xs font-medium text-gray-900 whitespace-nowrap">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="py-3.5 px-4 font-medium text-gray-900">
                                    {{ $invoice->customer_name }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="h-7 w-7 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/15 font-semibold text-[11px] flex items-center justify-center shrink-0">
                                            {{ $initials ?: 'U' }}
                                        </span>
                                        <span class="text-gray-700">{{ $creatorName }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
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
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td
                                    class="py-3.5 px-4 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                    <span
                                        class="text-xs font-medium text-gray-400 mr-1">{{ $invoice->currency }}</span>{{ number_format($invoice->total, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 tabular-nums whitespace-nowrap">
                                    {{ $invoice->issue_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-600 bg-indigo-50/70 hover:bg-indigo-100/80 transition">
                                        View
                                    </a>

                                    {{-- @if (auth()->user()->isAdmin())
                                        <button type="button"
                                            class="ml-1.5 inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 transition"
                                            onclick="document.getElementById('perm-{{ $invoice->id }}').classList.toggle('hidden')">
                                            Permissions
                                        </button>
                                    @endif --}}
                                </td>
                            </tr>

                            {{-- @if (auth()->user()->isAdmin())
                                <tr id="perm-{{ $invoice->id }}" class="hidden bg-gray-50/80">
                                    <td colspan="7" class="p-4">
                                        <form method="POST" action="{{ route('admin.permissions.store') }}"
                                            class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end bg-white p-4 rounded-xl ring-1 ring-gray-200/80">
                                            @csrf
                                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Employee</label>
                                                <select name="user_id" required
                                                    class="w-full rounded-lg border-gray-300 text-sm py-1.5 px-2.5">
                                                    @foreach ($employees as $emp)
                                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-3 text-sm md:col-span-1 pb-1.5">
                                                <label class="inline-flex items-center gap-1.5 text-gray-700">
                                                    <input type="checkbox" name="can_view" value="1" checked
                                                        class="rounded border-gray-300 text-indigo-600"> View
                                                </label>
                                                <label class="inline-flex items-center gap-1.5 text-gray-700">
                                                    <input type="checkbox" name="can_edit" value="1"
                                                        class="rounded border-gray-300 text-indigo-600"> Edit
                                                </label>
                                            </div>

                                            <div class="md:col-span-2 space-y-1">
                                                <label class="block text-xs font-medium text-gray-700">Expiry</label>
                                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-700">
                                                    <label class="inline-flex items-center gap-1">
                                                        <input type="radio" name="expiry_mode" value="forever" checked
                                                            onchange="this.closest('form').querySelector('[name=expires_at]').disabled = true">
                                                        Forever
                                                    </label>
                                                    <label class="inline-flex items-center gap-1">
                                                        <input type="radio" name="expiry_mode" value="date"
                                                            onchange="this.closest('form').querySelector('[name=expires_at]').disabled = false">
                                                        Until
                                                    </label>
                                                    <input type="datetime-local" name="expires_at" disabled
                                                        class="rounded-lg border-gray-300 p-1 text-xs flex-1">
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <button type="submit"
                                                    class="px-3.5 py-2 bg-indigo-600 text-white rounded-lg text-xs font-medium hover:bg-indigo-500 transition">
                                                    Grant
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif --}}
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center">
                                    <p class="text-sm font-medium text-gray-900">No invoices yet</p>
                                    <p class="text-xs text-gray-500 mt-1">Create your first invoice to see it listed
                                        here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($invoices->hasPages())
            <div class="pt-1">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
