<x-app-layout>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('user-select');
                const flags = [
                    ['can_view', 'canView'],
                    ['can_edit', 'canEdit'],
                    ['can_create', 'canCreate'],
                    ['can_delete', 'canDelete'],
                    ['can_approve', 'canApprove'],
                ];

                if (!select) return;

                function syncCheckboxes() {
                    const option = select.options[select.selectedIndex];

                    flags.forEach(([inputName, datasetKey]) => {
                        const checkbox = document.querySelector(`input[name="${inputName}"]`);
                        if (!checkbox) return;

                        const hasUser = Boolean(option && option.value);
                        checkbox.checked = hasUser && option.dataset[datasetKey] === '1';
                    });
                }

                select.addEventListener('change', syncCheckboxes);
                syncCheckboxes();
            });
        </script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl">Permissions</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold mb-3">Grant / Update Permissions</h3>

            <form method="POST" action="{{ route('admin.permissions.store') }}"
                class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                @php
                    $selectedUserId = old('user_id');
                @endphp

                {{-- User select --}}
                <div>
                    <label class="block text-sm font-medium">User *</label>
                    <select name="user_id" id="user-select" required class="w-full border rounded p-2">
                        <option value="" disabled {{ $selectedUserId ? '' : 'selected' }}>— Select a user —
                        </option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" data-can-view="{{ $u->can_view ? 1 : 0 }}"
                                data-can-edit="{{ $u->can_edit ? 1 : 0 }}"
                                data-can-create="{{ $u->can_create ? 1 : 0 }}"
                                data-can-delete="{{ $u->can_delete ? 1 : 0 }}"
                                data-can-approve="{{ $u->can_approve ? 1 : 0 }}"
                                {{ (string) $selectedUserId === (string) $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Flags --}}
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <label class="flex items-center gap-1">
                        <input type="checkbox" name="can_view" value="1"> View
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="checkbox" name="can_edit" value="1"> Edit
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="checkbox" name="can_create" value="1"> Create
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="checkbox" name="can_delete" value="1"> Delete
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="checkbox" name="can_approve" value="1"> Approve
                    </label>
                </div>

                {{-- Submit --}}
                <div class="md:col-span-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
                        Save permissions
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold mb-3">User Permissions</h3>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-gray-50">
                        <th class="p-2">User</th>
                        <th class="p-2">View</th>
                        <th class="p-2">Edit</th>
                        <th class="p-2">Create</th>
                        <th class="p-2">Delete</th>
                        <th class="p-2">Approve</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t">
                            <td class="p-2 font-semibold">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->can_view ? '✓' : '—' }}</td>
                            <td class="p-2">{{ $user->can_edit ? '✓' : '—' }}</td>
                            <td class="p-2">{{ $user->can_create ? '✓' : '—' }}</td>
                            <td class="p-2">{{ $user->can_delete ? '✓' : '—' }}</td>
                            <td class="p-2">{{ $user->can_approve ? '✓' : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
