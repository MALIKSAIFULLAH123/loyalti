<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class SeventTagData.
 * @mixin Builder
 * @property int    $id
 * @property int    $item_id
 * @property int    $tag_id
 * @property string $tag_text
 */
class SeventTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'sevent_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("/sevent?q={$this->id}");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("/sevent?q={$this->id}");
    }
}
