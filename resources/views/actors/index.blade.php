@extends('layouts.app', ['title' => 'My Actor Requests'])

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ __('actor.index.title') }}</h1>

    <div class="bg-white shadow rounded-2xl overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
            <tr class="text-left">
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.first_name') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.last_name') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.address') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.gender') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.height_cm') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('actor.index.headers.age') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($actors as $actor)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $actor->first_name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $actor->last_name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $actor->address ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $actor->gender->value ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $actor->height ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $actor->age ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                        {{ __('actor.index.empty') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
