<?php

namespace App\UseCases\HallData;

use App\Models\HallData;
use App\Exceptions\HallDataNotFoundException;

class UpdateAction
{
    public function __invoke($hallDataId, $is_high_setting): HallData
    {
        $hallData = HallData::find($hallDataId);

        if (!$hallData) {
            throw new HallDataNotFoundException('該当のデータが見つかりませんでした。');
        }

        $hallData->update([
            'is_high_setting' => $is_high_setting,
        ]);

        return $hallData;
    }
}
