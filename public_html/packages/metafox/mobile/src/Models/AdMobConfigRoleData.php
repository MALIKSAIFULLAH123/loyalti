<?php

namespace MetaFox\Mobile\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class AdMobConfigRoleData.
 *
 * @property int $id
 */
class AdMobConfigRoleData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    public const ENTITY_TYPE = 'ad_mob_config_role_data';

    protected $table = 'ad_mob_config_role_data';

    /** @var string[] */
    protected $fillable = [
        'id',
        'config_id',
        'role_id',
    ];
}

// end
