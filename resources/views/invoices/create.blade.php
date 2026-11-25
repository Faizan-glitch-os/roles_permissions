<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">New Invoice</h2></x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4">
        <form method="POST" action="{{ route('invoices.store') }}" class="bg-white p-6 rounded shadow">
            @csrf
            @include('invoices._form')
            <div class="mt-6 flex gap-3">
                <button class="px-4 py-2 bg-indigo-600 text-black rounded">Save Draft</button>
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 border rounded">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>