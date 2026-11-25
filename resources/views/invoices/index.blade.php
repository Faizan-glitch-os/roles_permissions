@push('scripts')
<script>
document.addEventListener('change', function (e) {
    const form = e.target.closest('form');
    if (!form) return;
    const dateInput = form.querySelector('[name=expires_at]');
    if (!dateInput) return;
    if (e.target.name === 'expiry_mode') {
        dateInput.disabled = e.target.value !== 'date';
    }
});
</script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Invoices</h2>
            @if($canCreate)
                <a href="{{ route('invoices.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-black rounded hover:bg-indigo-700">
                    + New Invoice
                </a>
            @else
                <span class="text-sm text-gray-500">You don't have permission to create invoices.</span>
            @endif
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4">
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr class="text-left">
                        <th class="p-3 text-left">#</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Created by</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Total</th>
                        <th class="p-3">Date</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr class="border-t">
                        <td class="p-3 font-mono">{{ $invoice->invoice_number }}</td>
                        <td class="p-3">{{ $invoice->customer_name }}</td>
                        <td class="p-3">{{ $invoice->creator->name }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs
                                @class([
                                    'bg-gray-200 text-gray-700' => $invoice->status === 'draft',
                                    'bg-yellow-200 text-yellow-800' => $invoice->status === 'pending_approval',
                                    'bg-green-200 text-green-800' => in_array($invoice->status, ['approved','generated']),
                                    'bg-red-200 text-red-800' => $invoice->status === 'rejected',
                                ])">
                                {{ str_replace('_',' ',$invoice->status) }}
                            </span>
                        </td>
                        <td class="p-3">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td class="p-3">{{ $invoice->issue_date->format('Y-m-d') }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
    <a href="{{ route('invoices.show', $invoice) }}"
       class="text-indigo-600 hover:underline">View</a>

    @if(auth()->user()->isAdmin())
        <button type="button"
                class="ml-2 text-purple-600 hover:underline"
                onclick="document.getElementById('perm-{{ $invoice->id }}').classList.toggle('hidden')">
            Permissions
        </button>
    @endif
</td>
                    </tr>
                    @if(auth()->user()->isAdmin())
<tr id="perm-{{ $invoice->id }}" class="hidden bg-gray-50">
    <td colspan="7" class="p-4">
        <form method="POST" action="{{ route('admin.permissions.store') }}"
              class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

            <div class="md:col-span-2">
                <label class="block text-xs font-medium">Employee</label>
                <select name="user_id" required class="w-full border rounded p-1 text-sm">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3 text-sm md:col-span-1">
                <label class="flex items-center gap-1">
                    <input type="checkbox" name="can_view" value="1" checked> View
                </label>
                <label class="flex items-center gap-1">
                    <input type="checkbox" name="can_edit" value="1"> Edit
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium">Expiry</label>
                <div class="flex gap-2 text-xs items-center">
                    <label><input type="radio" name="expiry_mode" value="forever" checked> Forever</label>
                    <label><input type="radio" name="expiry_mode" value="date"> Until</label>
                    <input type="datetime-local" name="expires_at" disabled
                           class="border rounded p-1 text-xs flex-1">
                </div>
            </div>

            <div>
                <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm w-full">Grant</button>
            </div>
        </form>
    </td>
</tr>
@endif
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-gray-500">No invoices.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
</x-app-layout>