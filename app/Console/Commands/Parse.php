<?php

namespace App\Console\Commands;

use App\Models\RawItem;
use App\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Parse extends Command
{
    protected $signature = 'parse:all';

    protected $description = 'Command description';

    public function handle()
    {
        $source = Source::query()->oldest()->first();
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $source->base_url, ['query' => [
            'page' => $source->page,
        ]]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {

            DB::beginTransaction();
            $content = json_decode($response->getBody(), true);
            
            foreach ($content['data']['items'] as $item) {
                RawItem::query()->firstOrCreate(
                    [
                        'source_id' => $source->id,
                        'external_id' => $item['car_id'],
                        'hash' => md5(json_encode($item)),
                    ],
                    [
                        'data' => $item,
                    ]);
            }

            $source->page = $content['data']['meta']['current_page'] + 1;
            if ($content['data']['meta']['current_page'] === $content['data']['meta']['last_page']) {
                $source->page = 1;
            }
            $source->save();
            DB::commit();
        }
        return Command::SUCCESS;
    }
}
