<?php

namespace App\Http\Controllers;

use App\Http\Resources\HallIndexResource;
use App\Http\Resources\HallEditResource;
use App\Http\Requests\Hall\UpdateRequest;
use App\UseCases\Hall\UpdateAction;
use Inertia\Inertia;
use App\Models\Hall;

class HallController extends Controller
{
    public function index()
    {
        return Inertia::render('Hall/Index', [
            'halls' => new HallIndexResource(Hall::get()),
        ]);
    }

    public function edit(Hall $hall)
    {
        return Inertia::render('Hall/Edit', [
            'hall' => new HallEditResource($hall),
        ]);
    }

    public function update(UpdateRequest $request, UpdateAction $action, Hall $hall)
    {
        $validated = $request->validated();

        try {
            $action($hall, $validated);

            return redirect()->route('halls.edit', ['hall' => $hall->id]);
        } catch (\Exception $e) {
            return abort(500);
        }
    }
}
