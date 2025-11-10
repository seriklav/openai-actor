@extends('layouts.base')

@section('title', trim($__env->yieldContent('page_title', 'Welcome')))

@section('content')
    <div class="mx-auto max-w-xl">
        @yield('page')
    </div>
@endsection
