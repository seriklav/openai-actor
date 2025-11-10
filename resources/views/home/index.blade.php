@extends('layouts.app')

@section('content')
    <div class="rounded-xl border bg-white p-6">
        <h1 class="text-xl font-semibold mb-2">{{ __('actor.form.title') }}</h1>

        <form method="POST" action="{{ route('actors.store') }}"
              class="bg-white p-6 rounded-2xl shadow space-y-6">
            @csrf

            <div>
                <x-label for="email" :value="__('actor.form.email.label')" />
                <x-input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="{{ __('actor.form.email.placeholder') }}"
                    value="{{ old('email', $email) }}"/>
                <x-error name="email" />
            </div>

            <div>
                <x-label for="description" :value="__('actor.form.description.label')" />
                <x-textarea
                    id="description"
                    name="description"
                    placeholder="{{ __('actor.form.description.placeholder') }}"
                />

                <p class="mt-2 text-xs text-gray-500">
                    {{ __('actor.form.description.help') }}
                </p>

                <x-error name="description" />
            </div>

            <x-button>{{ __('actor.form.submit') }}</x-button>
        </form>
    </div>
@endsection
