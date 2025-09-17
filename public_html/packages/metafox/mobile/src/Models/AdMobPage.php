<?php

namespace MetaFox\Mobile\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Mobile\Models\AdMobConfig as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AdMobPage.
 *
 * @property        int                 $id
 * @property        string              $path
 * @property        string              $name
 * @property        string              $module_id
 * @property        string              $package_id
 * @property        string              $created_at
 * @property        string              $updated_at
 * @property        ?Collection<Config> $configs
 * @method   static AdMobPageFactory    factory(...$parameters)
 */
class AdMobPage extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'ad_mob_page';

    protected $table = 'ad_mob_pages';

    /** @var string[] */
    protected $fillable = [
        'id',
        'name',
        'path',
        'module_id',
        'package_id',
        'created_at',
        'updated_at',
    ];

    public function configs(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::class,
            'ad_mob_config_page_data',
            'page_id',
            'config_id'
        )->using(AdMobConfigPageData::class);
    }

    public function getNameAttribute(?string $value = null): string
    {
        if (!$value) {
            return $value;
        }

        return __p($value);
    }
}

// end
