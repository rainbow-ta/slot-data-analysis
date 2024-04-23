<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flatHalls = $this->resource->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'external_url' => $item->external_url,
                'note' => $item->note,
                'updated_at' => Carbon::parse($item->updated_at)->toDateString(),
            ];
        });

        return $flatHalls->toArray();
    }
}
