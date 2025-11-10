@props(['id' => null, 'type' => 'text', 'name', 'value' => '', 'placeholder' => ''])
<input
    id="{{ $id ?? $name }}"
    type="{{ $type }}"
    name="{{ $name }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' =>
        'w-full rounded-lg border border-neutral-300 bg-white px-3 py-2
         focus:outline-none focus:ring-2 focus:ring-neutral-900'
    ]) }}
/>
