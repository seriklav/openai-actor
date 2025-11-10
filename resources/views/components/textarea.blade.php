@props([
    'id' => null,
    'name',
    'rows' => 6,
    'placeholder' => '',
    'value' => '',
    'required' => false,
])

<textarea
    id="{{ $id ?? $name }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    @if($required) required @endif
    {{ $attributes->merge([
        'class' =>
            'w-full rounded-xl border-gray-300 bg-white px-3 py-2
             focus:border-blue-500 focus:ring-2 focus:ring-blue-200'
    ]) }}
>{{ old($name, $value) }}</textarea>
