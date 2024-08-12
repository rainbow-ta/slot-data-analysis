<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Hall;
use App\Models\HallData;
use App\Models\SlotMachine;
use App\Services\HighSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ScrapeAnaslot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scrape-anaslot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapes Ana Slot data';

    private $highSettingService;

    public function __construct()
    {
        parent::__construct();
        $this->highSettingService = new HighSettingService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = new ConsoleOutput();
        $halls = Hall::whereNotNull('ana_slot_url_name')
            ->where('is_scrape', 1)
            ->select('id', 'ana_slot_url_name')
            ->get();

        if ($halls->isEmpty()) {
            $this->info('No halls found.');
            return;
        }

        $today = now();
        $monthsAgo = now()->subMonths(1);
        $currentDate = clone $monthsAgo;

        // プログレスバー開始
        $totalDays = $monthsAgo->diff($today)->days;
        $progressBar = new ProgressBar($output, $totalDays * count($halls));
        $progressBar->start();

        // データ取得処理
        while ($currentDate <= $today) {
            foreach ($halls as $hall) {
                $formattedCurrentDate = $currentDate->format('Y-m-d');
                $this->processHallData($hall, $formattedCurrentDate);

                $progressBar->advance();
            }

            $currentDate->addDay();
        }

        // プログレスバー終了
        $progressBar->finish();
        $output->writeln("\n");
        $this->info('Scraping completed successfully.');
    }

    private function processHallData($hall, $formattedCurrentDate)
    {
        $hallData = HallData::where('date', $formattedCurrentDate)
            ->where('hall_id', $hall->id)
            ->first();

        if ($hallData) {
            return;
        }

        $dataArray = $this->scrape($hall, $formattedCurrentDate);

        if ($dataArray) {
            $this->insertHallData($dataArray);
        }
    }

    private function scrape($hall, $formattedDate)
    {
        $client = new \GuzzleHttp\Client();
        $dataArray = [];

        // サイトの負荷を軽減するために待機時間を設定
        $sleepTime = rand(1, 5);
        sleep($sleepTime);

        $retryCount = 3;
        $url = "https://ana-slo.com/{$formattedDate}-{$hall->ana_slot_url_name}-data/";
        for ($i = 0; $i < $retryCount; $i++) {
            try {
                $response = $client->request('GET', $url);
                break;
            } catch (\Exception $e) {
                if ($e->getCode() === 404) {
                    return null;
                }

                if ($i < $retryCount - 1) {
                    sleep($sleepTime);
                } else {
                    Log::error($e->getMessage());
                    throw $e;
                }
            }
        }

        $html = $response->getBody()->getContents();
        $crawler = new Crawler($html);

        $tbodyFirst = $crawler->filter('tbody')->first();

        if ($tbodyFirst->filter('th')->count() > 0) {
            $tbody = new Crawler($crawler->filter('tbody')->eq(1)->html());
        } else {
            $tbody = new Crawler($tbodyFirst->html());
        }

        $tbody->filter('tr')->each(function (Crawler $row) use (&$dataArray, $formattedDate, $hall) {
            $rowData = [];

            $row->filter('td')->each(function (Crawler $cell) use (&$rowData) {
                $cellText = str_replace('1/', '', str_replace(',', '', $cell->text()));
                $rowData[] = $cellText;
            });

            $slotMachine = SlotMachine::firstOrCreate(['name' => $rowData[0]]);

            // 高設定かを判定
            $gameCount = $rowData[2] ?? null;
            $differenceCoins = $rowData[3] ?? null;
            $isHighSetting = $this->highSettingService->isHighSetting($gameCount, $differenceCoins);

            // 店舗によってデータ構造が異なるため、要素数で判定して取得する
            $countRowData = count($rowData);
            $currentTimestamp = Carbon::now();
            if ($countRowData === 9) {
                $dataArray[] = [
                    'slot_machines_id' => $slotMachine->id,
                    'slot_number' => $rowData[1] ?? null,
                    'game_count' => $gameCount,
                    'difference_coins' => $differenceCoins,
                    'big_bonus_count' => $rowData[4] ?? null,
                    'regular_bonus_count' => $rowData[5] ?? null,
                    'synthesis_probability' => $rowData[6] ?? null,
                    'big_bonus_probability' => $rowData[7] ?? null,
                    'regular_bonus_probability' => $rowData[8] ?? null,
                    'date' => $formattedDate,
                    'is_predicted_high_setting' => $isHighSetting,
                    'hall_id' => $hall->id,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            } else {
                $dataArray[] = [
                    'slot_machines_id' => $slotMachine->id,
                    'slot_number' => $rowData[1] ?? null,
                    'game_count' => $gameCount,
                    'difference_coins' => $differenceCoins,
                    'big_bonus_count' => $rowData[4] ?? null,
                    'regular_bonus_count' => $rowData[5] ?? null,
                    'art_count' => $rowData[6] ?? null,
                    'synthesis_probability' => $rowData[7] ?? null,
                    'big_bonus_probability' => $rowData[8] ?? null,
                    'regular_bonus_probability' => $rowData[9] ?? null,
                    'art_probability' => $rowData[10] ?? null,
                    'date' => $formattedDate,
                    'is_predicted_high_setting' => $isHighSetting,
                    'hall_id' => $hall->id,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            }
        });

        return $dataArray;
    }

    private function insertHallData($dataArray)
    {
        DB::beginTransaction();

        try {
            HallData::insert($dataArray);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            $this->error('Error inserting data: ' . $e->getMessage());
        }
    }
}
