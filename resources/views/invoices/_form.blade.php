@php
    $items = old('items', isset($invoice) ? $invoice->items->map(fn($i) => [
        'description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price,
    ])->all() : [['description' => '', 'quantity' => 1, 'unit_price' => 0]]);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Customer name *</label>
        <input type="text" name="customer_name" required
               value="{{ old('customer_name', $invoice->customer_name ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Customer email</label>
        <input type="email" name="customer_email"
               value="{{ old('customer_email', $invoice->customer_email ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Customer address</label>
        <textarea name="customer_address" rows="2" class="w-full border rounded p-2">{{ old('customer_address', $invoice->customer_address ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium">Issue date *</label>
        <input type="date" name="issue_date" required
               value="{{ old('issue_date', isset($invoice) ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Due date</label>
        <input type="date" name="due_date"
               value="{{ old('due_date', isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Currency</label>
        <input type="text" name="currency" maxlength="3" required
               value="{{ old('currency', $invoice->currency ?? 'PKR') }}"
               class="w-full border rounded p-2 uppercase">
    </div>
    <div>
        <label class="block text-sm font-medium">Tax rate (%)</label>
        <input type="number" step="0.01" name="tax_rate"
               value="{{ old('tax_rate', $invoice->tax_rate ?? 0) }}"
               class="w-full border rounded p-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Notes</label>
        <textarea name="notes" rows="2" class="w-full border rounded p-2">{{ old('notes', $invoice->notes ?? '') }}</textarea>
    </div>
</div>

<hr class="my-6">

<h3 class="font-semibold mb-2">Items</h3>
<table class="w-full" id="items-table">
    <thead>
        <tr class="text-left text-sm bg-gray-100">
            <th class="p-2">Description</th>
            <th class="p-2 w-24">Qty</th>
            <th class="p-2 w-32">Unit price</th>
            <th class="p-2 w-32">Total</th>
            <th class="p-2 w-16"></th>
        </tr>
    </thead>
    <tbody>
    @foreach($items as $i => $item)
        <tr class="item-row border-t">
            <td class="p-2"><input name="items[{{ $i }}][description]" value="{{ $item['description'] }}" required class="w-full border rounded p-1"></td>
            <td class="p-2"><input type="number" step="0.01" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}" required class="w-full border rounded p-1 qty"></td>
            <td class="p-2"><input type="number" step="0.01" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] }}" required class="w-full border rounded p-1 price"></td>
            <td class="p-2 text-right line-total">0.00</td>
            <td class="p-2"><button type="button" class="remove-row text-red-600">✕</button></td>
        </tr>
    @endforeach
    </tbody>
</table>

<button type="button" id="add-row" class="mt-2 text-indigo-600">+ Add item</button>

<script>
(function () {
    const tbody = document.querySelector('#items-table tbody');
    let idx = {{ count($items) }};

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const line = qty * price;
            row.querySelector('.line-total').textContent = line.toFixed(2);
            subtotal += line;
        });
    }

    document.getElementById('add-row').addEventListener('click', () => {
        const row = tbody.querySelector('.item-row').cloneNode(true);
        row.querySelectorAll('input').forEach(inp => {
            inp.name = inp.name.replace(/\[\d+\]/, `[${idx}]`);
            inp.value = inp.classList.contains('qty') ? 1 : '';
        });
        row.querySelector('.line-total').textContent = '0.00';
        tbody.appendChild(row);
        idx++;
    });

    tbody.addEventListener('click', e => {
        if (e.target.classList.contains('remove-row') && tbody.children.length > 1) {
            e.target.closest('tr').remove();
            recalc();
        }
    });

    tbody.addEventListener('input', recalc);
    recalc();
})();
</script>