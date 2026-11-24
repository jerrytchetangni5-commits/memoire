@props(['type' => 'error'])

<p
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    class="mb-4 rounded-lg border px-3 py-2 text-sm font-medium {{ $type === 'error' ? 'border-red-700 bg-red-50 text-red-700' : 'border-green-800 bg-green-50 text-green-800' }}"
>
    {{ $slot }}
</p>