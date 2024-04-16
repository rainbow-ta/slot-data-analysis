<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallDataResource extends JsonResource
{
    protected $hallCollection;
    protected $date;

    public function __construct($hallCollection, $date)
    {
        $this->hallCollection = $hallCollection;
        $this->date = $date;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flatHallData = $this->hallCollection['hallData']->map(function ($item) {
            return [
                'id' => $item->id,
                'date' => $item->date,
                'slot_number' => $item->slot_number,
                'slot_machine_name' => $item->slotMachine->name,
                'is_high_setting' => $item->is_high_setting,
            ];
        });

        return [
            'hall' => $this->hallCollection['hall'],
            'hallData' => $flatHallData,
            'date' => $this->date,
        ];
    }
}
