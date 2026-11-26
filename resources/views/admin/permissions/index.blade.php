<x-app-layout>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('user-select');
                const selectAllBtn = document.getElementById('select-all-perms');
                const clearAllBtn = document.getElementById('clear-all-perms');
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

                if (selectAllBtn) {
                    selectAllBtn.addEventListener('click', function() {
                        flags.forEach(([inputName]) => {
                            const checkbox = document.querySelector(`input[name="${inputName}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    });
                }

                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', function() {
                        flags.forEach(([inputName]) => {
                            const checkbox = document.querySelector(`input[name="${inputName}"]`);
                            if (checkbox) checkbox.checked = false;
                        });
                    });
                }
            });
        </script>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Permissions & Access Control
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Assign or update module capabilities for team members.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        {{-- Grant / Update Card --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Grant / Update Permissions</h3>
                    <p class="text-xs text-gray-500">Selecting a user automatically loads their current permissions.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.permissions.store') }}" class="p-6 space-y-6">
                @csrf

                @php
                    $selectedUserId = old('user_id');
                    $permissionLabels = [
                        'can_view' => ['label' => 'View', 'desc' => 'Read records'],
                        'can_edit' => ['label' => 'Edit', 'desc' => 'Modify existing'],
                        'can_create' => ['label' => 'Create', 'desc' => 'Add new entries'],
                        'can_delete' => ['label' => 'Delete', 'desc' => 'Remove records'],
                        'can_approve' => ['label' => 'Approve', 'desc' => 'Sign off & verify'],
                    ];
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {{-- User select --}}
                    <div class="lg:col-span-5">
                        <label for="user-select" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Target User <span class="text-rose-500">*</span>
                        </label>
                        <select name="user_id" id="user-select" required
                            class="w-full rounded-xl border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
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

                    {{-- Permission flags --}}
                    <div class="lg:col-span-7">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Permission Flags</span>
                            <div class="flex items-center gap-3 text-xs font-medium">
                                <button type="button" id="select-all-perms"
                                    class="text-indigo-600 hover:text-indigo-700 transition">
                                    Select all
                                </button>
                                <span class="text-gray-300">|</span>
                                <button type="button" id="clear-all-perms"
                                    class="text-gray-500 hover:text-gray-700 transition">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach ($permissionLabels as $field => $meta)
                                <label
                                    class="group relative flex items-start gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/40 hover:bg-indigo-50/40 hover:border-indigo-200 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400 cursor-pointer transition">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="leading-tight">
                                        <span
                                            class="block text-sm font-medium text-gray-900">{{ $meta['label'] }}</span>
                                        <span class="block text-[11px] text-gray-500">{{ $meta['desc'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Submit bar --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Save permissions
                    </button>
                </div>
            </form>
        </div>

        {{-- Overview Table Card --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">User Permissions Matrix</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                    {{ count($users) }} {{ \Illuminate\Support\Str::plural('user', count($users)) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/40 text-xs uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-6 font-semibold">User</th>
                            <th class="py-3.5 px-4 font-semibold text-center">View</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Edit</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Create</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Delete</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Approve</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            @php
                                $flagsList = [
                                    $user->can_view,
                                    $user->can_edit,
                                    $user->can_create,
                                    $user->can_delete,
                                    $user->can_approve,
                                ];
                                $initials = collect(explode(' ', trim($user->name)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-xs flex items-center justify-center shrink-0">
                                            {{ $initials ?: 'U' }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                            @if (!empty($user->email))
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @foreach ($flagsList as $enabled)
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($enabled)
                                            <span
                                                class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Allowed
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">
                                                —
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 px-6 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
