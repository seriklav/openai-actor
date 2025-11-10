<?php

namespace App\Http\Controllers\Home;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $email = $user?->email ?: null;

        return view('home.index', compact('email'));
    }
}
