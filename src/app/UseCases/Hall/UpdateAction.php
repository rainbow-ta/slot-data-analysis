<?php

namespace App\UseCases\Hall;

use App\Models\Hall;

class UpdateAction
{
    public function __invoke($hall, $validated): Hall
    {
        $hall->update([
            'is_scrape' => $validated['is_scrape'],
            'note' => $validated['note'],
            'external_url' => $validated['external_url'],
        ]);

        return $hall;
    }
}
