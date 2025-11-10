<button {{ $attributes->merge(['class' =>
    'inline-flex items-center justify-center rounded-lg bg-black px-4 py-2 text-white
     hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed'
]) }}>
    {{ $slot }}
</button>
