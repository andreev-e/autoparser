<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawItem extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'external_id',
        'source_id',
        'hash',
        'data',
        'is_basic',
    ];

    protected $casts = [
        'data' => 'array',
        'is_basic' => 'boolean',
    ];

    public static function getLastState($sourceId, $externalId): self|null
    {
        $items = RawItem::query()->where([
            'source_id' => $sourceId,
            'external_id' => $externalId,
        ])->oldest()->get();

        if (count($items)) {
            $base = $items->first();
            foreach ($items as $item) {
                $base->data = array_merge($base->data, $item->data);
            }
            return $base;
        }

        return null;
    }
}
