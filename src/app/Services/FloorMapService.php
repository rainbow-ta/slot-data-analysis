<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Typography\FontFactory;

class FloorMapService
{
    private const IMAGE_WIDTH = 1280;
    private const IMAGE_HEIGHT = 2800;
    private const RECT_WIDTH = 65;
    private const RECT_HEIGHT = 75;
    private const FONT_SIZE = 12;
    private const FONT_COLOR = '#000000';
    private const BORDER_COLOR = 'gray';
    private const BORDER_SIZE = 2;
    private const BG_COLOR = '#ffffff';

    private $floorMapData;

    public function __construct($floorMapData)
    {
        $this->floorMapData = $floorMapData;
    }

    public function generateFloorMap($searchQuery)
    {
        $fullImagePath = public_path("/images/floor_map/$searchQuery.png");
        $relativeImagePath = "/images/floor_map/$searchQuery.png";

        if (file_exists($fullImagePath)) {
            return $relativeImagePath;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->create(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)->fill(self::BG_COLOR);

        $coordinate = [
            'x' => 0,
            'y' => 0,
            'increment_x' => 0,
            'increment_y' => 0,
            'last_increment_pattern' => '',
        ];

        foreach ($this->floorMapData as $data) {
            $coordinate = $this->settingCoordinate($coordinate, $data['slot_number']);

            if ($coordinate['x'] === 0) continue;

            $text = $this->generateText($data);
            $color = $this->generateColor($data['highSettingPercentage']);

            $this->drawRectangle($image, $coordinate['x'], $coordinate['y'], $color);
            $this->drawText($image, $text, $coordinate['x'], $coordinate['y']);
        }

        $image->save($fullImagePath);

        return $relativeImagePath;
    }

    private function generateText($data)
    {
        $slotMachineName = $this->generateSlotMachineName($data['slot_machine_name']);

        return <<<EOD
        {$data['slot_number']}
        $slotMachineName
        {$data['highSettingPercentage']}%({$data['highSettingCount']}/{$data['count']})
        EOD;
    }

    private function drawRectangle($image, $x, $y, $color)
    {
        $image->drawRectangle($x, $y, function (RectangleFactory $rectangle) use ($color) {
            $rectangle->size(self::RECT_WIDTH, self::RECT_HEIGHT);
            $rectangle->background($color);
            $rectangle->border(self::BORDER_COLOR, self::BORDER_SIZE);
        });
    }

    private function drawText($image, $text, $x, $y)
    {
        $image->text($text, $x + 5, $y + self::RECT_HEIGHT - 10, function(FontFactory $font) {
            $font->filename(public_path('fonts/NotoSansJP-VariableFont_wght.ttf'));
            $font->size(self::FONT_SIZE);
            $font->color(self::FONT_COLOR);
        });
    }

    private function generateSlotMachineName($slotMachineName)
    {
        $lines = [];
        $maxLines = 3;

        for ($i = 0; $i < mb_strlen($slotMachineName, 'UTF-8'); $i += 4) {
            $lines[] = mb_substr($slotMachineName, $i, 4, 'UTF-8');
        }

        return implode("\n", array_slice($lines, 0, $maxLines));
    }

    private function settingCoordinate($coordinate, $slotNumber)
    {
        $patterns = [
            // ['台番号', 'x軸開始座標', 'y軸開始座標', 'x軸移動量', 'y軸移動量']
            'aa' => [range(1001, 1016), 1025, 1100, -65, 0],
            'ab' => [range(1017, 1032), 50, 875, 65, 0],
            'ac' => [range(1033, 1048), 1025, 800, -65, 0],
            'ad' => [range(1049, 1064), 50, 600, 65, 0],
            'ae' => [range(1065, 1067), 1025, 525, -65, 0],
            'af' => [range(1068, 1074), 700, 525, -65, 0],
            'ag' => [range(1075, 1077), 180, 525, -65, 0],
            'ah' => [range(1078, 1093), 50, 325, 65, 0],
            'ai' => [range(1094, 1109), 1025, 250, -65, 0],
            'aj' => [range(1110, 1125), 50, 50, 65, 0],
            'ak' => [range(1126, 1140), 1200, 50, 0, 75],
            'al' => [range(1141, 1146), 50, 2000, 0, -75],
            'am' => [range(1147, 1147), 115, 1550, 65, 0],
            'an' => [range(1148, 1162), 180, 1475, 65, 0],
            'ao' => [range(1163, 1168), 1200, 1475, 0, 75],
            'ap' => [range(1169, 1173), 1200, 2300, 0, 75],
            'aq' => [range(1174, 1185), 1025, 2600, -65, 0],
            'ar' => [range(1186, 1195), 310, 2375, 65, 0],
            'as' => [range(1196, 1205), 895, 2300, -65, 0],
            'at' => [range(1206, 1217), 310, 2075, 65, 0],
            'au' => [range(1218, 1219), 1025, 2000, -65, 0],
            'av' => [range(1220, 1226), 830, 2000, -65, 0],
            'aw' => [range(1227, 1238), 310, 1775, 65, 0],
            'ax' => [range(1239, 1250), 1025, 1700, -65, 0],
        ];

        foreach ($patterns as $pattern => [$range, $xStart, $yStart, $incX, $incY]) {
            if (in_array($slotNumber, $range, true)) {
                $coordinate = $this->updateIncrement($coordinate, $pattern);
                $coordinate['x'] = $xStart + $coordinate['increment_x'];
                $coordinate['y'] = $yStart + $coordinate['increment_y'];
                $coordinate['increment_x'] += $incX;
                $coordinate['increment_y'] += $incY;
                break;
            }
        }

        return $coordinate;
    }

    private function updateIncrement($coordinate, $pattern)
    {
        if ($coordinate['last_increment_pattern'] !== $pattern) {
            $coordinate['last_increment_pattern'] = $pattern;
            $coordinate['increment_x'] = 0;
            $coordinate['increment_y'] = 0;
        }

        return $coordinate;
    }

    private function generateColor($percentage)
    {
        if ($percentage >= 75) {
            return '#FF5192';
        } elseif ($percentage >= 50) {
            return '#98FB98';
        } elseif ($percentage >= 25) {
            return '#75A9FF';
        } else {
            return '';
        }
    }
}
