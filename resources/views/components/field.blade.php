@props(['id', 'label', 'hint' => null, 'type' => 'text', 'value' => null])

@php
    $error = $errors->first($id);
    $describedBy = trim(($hint ? "{$id}-hint " : '') . ($error ? "{$id}-error" : ''));
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-900">{{ $label }}</label>

    <input
        id="{{ $id }}"
        name="{{ $id }}"
        type="{{ $type }}"
        value="{{ $value ?? old($id) }}"
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->class([
            'h-12 w-full rounded-lg border bg-white px-3 text-base text-slate-900 placeholder:text-slate-500',
            'border-red-700' => $error,
            'border-slate-300' => ! $error,
        ]) }}
    >

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-slate-600">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" role="alert" class="mt-1.5 text-sm font-medium text-red-700">{{ $error }}</p>
    @endif
</div>