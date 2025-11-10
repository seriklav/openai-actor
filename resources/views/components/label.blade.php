@props(['for', 'value'])
<label for="{{ $for }}" class="block text-sm font-medium text-neutral-700 mb-1">
    {{ $value ?? $slot }}
</label>
