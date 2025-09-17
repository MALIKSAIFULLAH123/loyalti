<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class CollectionStatistic.
 *
 * @property int      $id
 * @property string   $item_type
 * @property int      $item_id
 * @property int      $total_photo
 * @property int      $total_video
 * @property ?Content $item
 * @property array    $data
 */
class CollectionStatistic extends Model implements Entity, HasTotalItem
{
    use HasEntity;
    use HasItemMorph;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'photo_collection_statistic';

    protected $table = 'photo_collection_statistic';

    /** @var string[] */
    protected $guarded = [];

    public $timestamps = false;

    public function incrementTotalColumn(string $type, int $amount = 1): int
    {
        $column = "total_$type";
        if (!array_key_exists($column, $this->attributes)) {
            return 0;
        }

        return $this->incrementAmount($column, $amount);
    }

    public function decrementTotalColumn(string $type, int $amount = 1): int
    {
        $column = "total_$type";
        if (!array_key_exists($column, $this->attributes)) {
            return 0;
        }

        return $this->decrementAmount($column, $amount);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAggregateData(): array
    {
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (Str::contains($key, 'total_')) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

// end
