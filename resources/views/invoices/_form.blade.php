@php
    $items = old(
        'items',
        isset($invoice)
            ? $invoice->items
                ->map(
                    fn($i) => [
                        'description' => $i->description,
                        'quantity' => $i->quantity,
                        'unit_price' => $i->unit_price,
                    ],
                )
                ->all()
            : [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
    );
@endphp

<div class="space-y-8">
    {{-- Customer & Billing Details --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
            <h3 class="text-base font-semibold text-gray-900">Customer & Billing Details</h3>
            <p class="text-xs text-gray-500 mt-0.5">Enter client contact info, billing dates, currency, and tax rate.</p>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Customer name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="customer_name" required
                    value="{{ old('customer_name', $invoice->customer_name ?? '') }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer email</label>
                <input type="email" name="customer_email"
                    value="{{ old('customer_email', $invoice->customer_email ?? '') }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer address</label>
                <textarea name="customer_address" rows="2"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">{{ old('customer_address', $invoice->customer_address ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Issue date <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="issue_date" required
                    value="{{ old('issue_date', isset($invoice) ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 tabular-nums shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Due date</label>
                <input type="date" name="due_date"
                    value="{{ old('due_date', isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 tabular-nums shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency</label>
                <input type="text" name="currency" maxlength="3" required
                    value="{{ old('currency', $invoice->currency ?? 'PKR') }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm font-mono text-gray-900 uppercase shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tax rate (%)</label>
                <input type="number" step="0.01" min="0" name="tax_rate" id="tax-rate-input"
                    value="{{ old('tax_rate', $invoice->tax_rate ?? 0) }}"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 tabular-nums shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">{{ old('notes', $invoice->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Line Items</h3>
                <p class="text-xs text-gray-500 mt-0.5">Add products or services to calculate invoice totals.</p>
            </div>
            <button type="button" id="add-row"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition">
                <span>+</span>
                <span>Add item</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="items-table">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/40 text-xs uppercase tracking-wider text-gray-500">
                        <th class="py-3 px-6 font-semibold">Description</th>
                        <th class="py-3 px-3 font-semibold w-28 text-center">Qty</th>
                        <th class="py-3 px-3 font-semibold w-36 text-right">Unit price</th>
                        <th class="py-3 px-4 font-semibold w-36 text-right">Total</th>
                        <th class="py-3 pr-6 pl-2 w-14"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $i => $item)
                        <tr class="item-row hover:bg-gray-50/50 transition">
                            <td class="py-3 px-6">
                                <input name="items[{{ $i }}][description]" value="{{ $item['description'] }}"
                                    required placeholder="Item description"
                                    class="w-full rounded-lg border-gray-300 bg-white py-1.5 px-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </td>
                            <td class="py-3 px-3">
                                <input type="number" step="0.01" min="0"
                                    name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}"
                                    required
                                    class="w-full rounded-lg border-gray-300 bg-white py-1.5 px-2.5 text-sm text-right text-gray-900 tabular-nums qty focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </td>
                            <td class="py-3 px-3">
                                <input type="number" step="0.01" min="0"
                                    name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] }}"
                                    required
                                    class="w-full rounded-lg border-gray-300 bg-white py-1.5 px-2.5 text-sm text-right text-gray-900 tabular-nums price focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums line-total">
                                0.00
                            </td>
                            <td class="py-3 pr-6 pl-2 text-right">
                                <button type="button" title="Remove row"
                                    class="remove-row inline-flex items-center justify-center h-7 w-7 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Live Totals Footer --}}
        <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-100 flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex items-center justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span id="invoice-subtotal" class="font-medium text-gray-900 tabular-nums">0.00</span>
                </div>
                <div class="flex items-center justify-between text-gray-600">
                    <span>Tax (<span
                            id="invoice-tax-rate">{{ old('tax_rate', $invoice->tax_rate ?? 0) }}</span>%)</span>
                    <span id="invoice-tax-amount" class="font-medium text-gray-900 tabular-nums">0.00</span>
                </div>
                <div
                    class="pt-2 border-t border-gray-200 flex items-center justify-between text-base font-bold text-gray-900">
                    <span>Estimated Total</span>
                    <span id="invoice-grand-total" class="tabular-nums">0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const tbody = document.querySelector('#items-table tbody');
        const taxInput = document.getElementById('tax-rate-input');
        const subtotalEl = document.getElementById('invoice-subtotal');
        const taxRateEl = document.getElementById('invoice-tax-rate');
        const taxAmountEl = document.getElementById('invoice-tax-amount');
        const grandTotalEl = document.getElementById('invoice-grand-total');
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

            const taxRate = parseFloat(taxInput?.value) || 0;
            const taxAmount = subtotal * (taxRate / 100);
            const total = subtotal + taxAmount;

            if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
            if (taxRateEl) taxRateEl.textContent = taxRate;
            if (taxAmountEl) taxAmountEl.textContent = taxAmount.toFixed(2);
            if (grandTotalEl) grandTotalEl.textContent = total.toFixed(2);
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
            recalc();
        });

        tbody.addEventListener('click', e => {
            const btn = e.target.closest('.remove-row');
            if (btn && tbody.children.length > 1) {
                btn.closest('tr').remove();
                recalc();
            }
        });

        tbody.addEventListener('input', recalc);
        if (taxInput) taxInput.addEventListener('input', recalc);
        recalc();
    })();
</script>
