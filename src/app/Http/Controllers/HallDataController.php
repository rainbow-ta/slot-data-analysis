<?php

namespace App\Http\Controllers;

use App\Exceptions\HallDataNotFoundException;
use App\Http\Requests\HallData\IndexRequest;
use App\Http\Requests\HallData\UpdateRequest;
use App\Http\Resources\HallDataResource;
use App\UseCases\HallData\IndexAction;
use App\UseCases\HallData\UpdateAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hall;
use App\Models\HallData;
use App\Services\FloorMapService;
use App\Services\HallDataService;
use Carbon\Carbon;

class HallDataController extends Controller
{
    public function index(IndexRequest $request, IndexAction $action, $hallId)
    {
        $validated = $request->validated();

        $date = $validated['date'] ?? Carbon::today()->format('Y-m-d');

        return Inertia::render('HallData/Index', [
            'hallCollection' => new HallDataResource($action($hallId, $date), $date),
        ]);
    }

    public function update(UpdateRequest $request, UpdateAction $action, $hallId, $hallDataId)
    {
        $validated = $request->validated();

        try {
            $action($hallDataId, $validated['is_high_setting']);

            return redirect()->route('halls.hall-data.index', ['hall' => $hallId, 'date' => $validated['date']]);
        } catch (HallDataNotFoundException $e) {
            return abort(404, $e->getMessage());
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function detail(Request $request)
    {
        // requestを変数に格納
        $hallId = $request->hall;
        $startDate = $request->startDate ?? now()->subDays(7)->startOfDay()->toDateString();
        $endDate = $request->endDate ?? now()->endOfDay()->toDateString();
        $selectedDates = $request->selectedDates ?? [];
        $slotMachineNameArray = $request->query('slotMachineNameArray', []);
        $dataType = $request->dataType ?? 'all';

        // ホールデータを取得
        $hallDataService = new HallDataService($dataType);
        $hallData = $hallDataService->fetchHallData($hallId, $startDate, $endDate, $selectedDates, $slotMachineNameArray);

        // フロアマップの生成
        $hall = Hall::whereId($hallId)->first();
        $floorMapImagePath = '';
        if ($hallId == 1) {
            $floorMapData = $hallDataService->calculateFloorMapData($hallData);
            $floorMapService = new FloorMapService($floorMapData);
            $searchQuery = $hall->name . $startDate . $endDate . implode($selectedDates) . implode($slotMachineNameArray) . $dataType;
            $floorMapImagePath = $floorMapService->generateFloorMap($searchQuery);
        }

        // 高設定データを台番号ごとに集計
        $highSettingSlotNumbers = $hallDataService->calculateHighSettingSlotNumbers($hallData);

        // 高設定データを台番号末尾ごとに集計
        $matstubiArray = $hallDataService->matsubiCount($hallData);

        // 全てのデータを取得
        $allDateData = $hallDataService->getAllDateData($hallData);

        return Inertia::render('HallData/Detail', [
            'hall' => $hall,
            'matsubiArray' => $hallDataService->matsubiCount($hallData),
            'matsubiTotals' => $hallDataService->matsubiTotals($matstubiArray),
            'highSettingSlotNumbers' => $highSettingSlotNumbers,
            'highSettingMachines' => $hallDataService->calculateHighSettingMachines($hallData),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'selectedAllDates' => HallData::whereHallId($hallId)->distinct('date')->orderBy('date', 'desc')->pluck('date'),
            'allDateData' => $allDateData,
            'uniqueDateCount' => $hallData->unique('date')->count(),
            'slumpSlotNumbers' => $hallDataService->calculateSlumpSlotNumbers($allDateData),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedDates' => $selectedDates,
            'slotMachineName' => implode(',', $slotMachineNameArray),
            'dataType' => $dataType,
            'floorMapImagePath' => $floorMapImagePath,
        ]);
    }
}
