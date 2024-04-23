<?php

namespace App\UseCases\HallData;

use App\Models\Hall;
use App\Models\HallData;
use Illuminate\Support\Collection;

class IndexAction
{
    public function __invoke($hallId, $date): Collection
    {
        $hall = Hall::select('id', 'name')->find($hallId);

        $hallData = HallData::with('slotMachine')
            ->whereHallId($hallId)
            ->whereDate('date', $date)
            ->orderBy('slot_number')
            ->get();

        return collect([
            'hall' => $hall,
            'hallData' => $hallData,
        ]);
    }
}
