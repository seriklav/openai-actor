<?php

use App\Http\Controllers\Actor\ActorIndexController;
use App\Http\Controllers\Actor\ActorStoreController;
use App\Http\Controllers\Home\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('actors')
    ->name('actors.')
    ->group(function () {
        Route::get('/', ActorIndexController::class)
            ->name('index')
            ->middleware('auth');

        Route::post('/', ActorStoreController::class)->name('store');
    });
