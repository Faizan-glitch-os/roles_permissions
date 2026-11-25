<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Employee Permissions</h2></x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold mb-3">Grant / Update Permission</h3>
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Employee *</label>
                    <select name="user_id" required class="w-full border rounded p-2">
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Invoice (leave blank = ALL)</label>
                    <select name="invoice_id" class="w-full border rounded p-2">
                        <option value="">— All invoices —</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <label><input type="checkbox" name="can_view" value="1"> View</label>
                    <label><input type="checkbox" name="can_edit" value="1"> Edit</label>
                    <label><input type="checkbox" name="can_create" value="1"> Create</label>
                </div>
                <div>
                    <label class="block text-sm font-medium">Expires at (optional)</label>
                    <input type="datetime-local" name="expires_at" class="w-full border rounded p-2">
                </div>
                <div class="md:col-span-2">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save permission</button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold mb-3">Existing Permissions</h3>
            @foreach($employees as $employee)
                <div class="mb-4">
                    <div class="font-semibold">{{ $employee->name }}</div>
                    @php $perms = $permissions[$employee->id] ?? collect(); @endphp
                    @if($perms->isEmpty())
                        <div class="text-sm text-gray-500">No explicit permissions.</div>
                    @else
                        <table class="w-full text-sm mt-2">
                            <thead><tr class="text-left bg-gray-50">
                                <th class="p-2">Invoice</th><th class="p-2">View</th><th class="p-2">Edit</th>
                                <th class="p-2">Create</th><th class="p-2">Expires</th><th class="p-2"></th>
                            </tr></thead>
                            <tbody>
                            @foreach($perms as $perm)
                                <tr class="border-t">
                                    <td class="p-2">{{ $perm->invoice?->invoice_number ?? 'ALL' }}</td>
                                    <td class="p-2">{{ $perm->can_view ? '✓' : '—' }}</td>
                                    <td class="p-2">{{ $perm->can_edit ? '✓' : '—' }}</td>
                                    <td class="p-2">{{ $perm->can_create ? '✓' : '—' }}</td>
                                    <td class="p-2">
                                        {{ $perm->expires_at?->format('Y-m-d H:i') ?? 'never' }}
                                        @if($perm->expires_at && $perm->expires_at->isPast())
                                            <span class="text-xs text-red-600">(expired)</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-right">
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 text-xs">Revoke</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>