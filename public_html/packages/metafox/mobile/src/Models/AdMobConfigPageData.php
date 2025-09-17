<?php

namespace MetaFox\Mobile\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class AdMobConfigPageData.
 *
 * @property int $id
 */
class AdMobConfigPageData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    public const ENTITY_TYPE = 'ad_mob_config_page_data';

    protected $table = 'ad_mob_config_page_data';

    /** @var string[] */
    protected $fillable = [
        'id',
        'config_id',
        'config_type',
        'page_id',
    ];
}

// end
