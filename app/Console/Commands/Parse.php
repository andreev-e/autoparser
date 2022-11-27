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

    protected $pages = 5;

    public function handle()
    {
        $source = Source::query()->oldest()->first();
        $client = new \GuzzleHttp\Client();

        for ($i = 0; $i < $this->pages; $i++) {
            $response = $client->request('GET', $source->base_url, ['query' => [
                'Page' => $source->page,
            ]]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {

                DB::beginTransaction();
                $content = json_decode($response->getBody(), true);
                foreach ($content['data']['items'] as $item) {
                    foreach ($item as $key => $itemDatum) {
                        $item[$key] = json_encode($itemDatum);
                    }

                    $found = RawItem::getLastState($source->id, $item['car_id']);
                    if ($found) {
                        $fullDiff = array_merge(array_diff($found->data, $item), array_diff($item, $found->data));
                        if (count($fullDiff) > 0) {
                            RawItem::query()->create(
                                [
                                    'source_id' => $source->id,
                                    'external_id' => $item['car_id'],
                                    'data' => $fullDiff,
                                ]);
                            $this->info('Update');
                        } else {
                            $this->info('No diff');
                        }
                    } else {
                        RawItem::query()->create(
                            [
                                'source_id' => $source->id,
                                'external_id' => $item['car_id'],
                                'data' => $item,
                                'is_basic' => true,
                            ]);
                    }
                }
                $source->page = $source->page + 1;
                if ($content['data']['meta']['current_page'] === $content['data']['meta']['last_page']) {
                    $source->page = 1;
                }
                $source->save();
                DB::commit();
                $this->info($source->name . ' p.' . $source->page - 1);

            } else {
                $this->error('HTTP ' . $statusCode);
            }
        }
        return Command::SUCCESS;
    }
}
