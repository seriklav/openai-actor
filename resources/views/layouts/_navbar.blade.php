<nav class="border-b bg-white/80 backdrop-blur sticky top-0 z-40">
    <div class="container mx-auto max-w-5xl px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-semibold"> {{ config('app.name','Laravel') }} </a>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('actors.index') }}" class="px-3 py-1 rounded-lg hover:bg-neutral-100">
                    {{ __('actor.nav.actors') }}
                </a>
            @endauth
        </div>
    </div>
</nav>
