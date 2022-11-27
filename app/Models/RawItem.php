<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function getStageState(): self|null
    {
        return $this->getState($this->source_id, $this->external_id, $this->id);
    }

    public static function getState($sourceId, $externalId, $stateId = null): self|null
    {
        $base = RawItem::query()->firstWhere([
            'source_id' => $sourceId,
            'external_id' => $externalId,
            'is_basic' => true,
        ]);

        $changes = RawItem::query()->where([
            'source_id' => $sourceId,
            'external_id' => $externalId,
            'is_basic' => false,
        ])
            ->when($stateId, function (Builder $query) use ($stateId){
                return $query->where('id', '<', $stateId);
            })
            ->oldest()->get();

        foreach ($changes as $change) {
            $base->data = array_merge($base->data, $change->data);
            $base->created_at = $change->created_at;
            $base->id = $change->id;
        }

        return $base;
    }

    public function onlyBasic($option = true): Builder
    {
        return $this->query()->where('is_basic', $option);
    }

    public function changes()
    {
        return $this->query()
            ->whereNot('id', $this->id)
            ->where('external_id', $this->external_id)
            ->get();
    }
}
