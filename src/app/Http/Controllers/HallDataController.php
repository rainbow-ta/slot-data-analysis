<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hall;
use App\Services\HallDataService;

class HallDataController extends Controller
{
    public function index()
    {
        return Inertia::render('HallData/Index', [
            'halls' => Hall::get(),
        ]);
    }

}
