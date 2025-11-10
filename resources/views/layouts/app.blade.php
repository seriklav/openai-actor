@extends('layouts.base')

@section('title', trim($__env->yieldContent('page_title', 'Dashboard')))

@section('content')
    <div class="mx-auto max-w-xl">
        @yield('page')
    </div>
@endsection
