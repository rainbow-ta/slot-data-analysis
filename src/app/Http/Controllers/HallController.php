<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Hall;

class HallController extends Controller
{
    public function index()
    {
        return Inertia::render('Hall/Index', [
            'halls' => Hall::get(),
        ]);
    }
}
