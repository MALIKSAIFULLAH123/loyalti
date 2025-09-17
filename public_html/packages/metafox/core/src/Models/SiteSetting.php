<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Database\Factories\SiteSettingFactory;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class SiteSetting.
 *
 * @property int    $id
 * @property string $module_id
 * @property string $name
 * @property string $config_name
 * @property bool   $is_auto
 * @property mixed  $value_actual
 * @property string $value_default
 * @property string $created_at
 * @property string $updated_at
 * @property int    $is_public
 * @property string $type
 * @property string $env_var
 * @property mixed  $value
 * @mixin Builder
 * @method SiteSettingFactory factory()
 */
class SiteSetting extends Model
{
    use HasEntity;
    use HasFactory;

    /** @var string */
    public const ENTITY_TYPE = 'site_setting';

    /** @var string */
    protected $table = 'core_site_settings';

    /** @var string[] */
    protected $casts = [
        'value_actual'  => 'array',
        'value_default' => 'array',
        'is_auto'       => 'boolean',
    ];

    /** @var string[] */
    protected $fillable = [
        'module_id',
        'name',
        'config_name',
        'package_id',
        'type',
        'is_auto',
        'value_actual',
        'value_default',
        'is_public',
        'env_var',
    ];

    /**
     * @return SiteSettingFactory
     */
    protected static function newFactory()
    {
        return SiteSettingFactory::new();
    }

    protected static function booted()
    {
        self::saving(function (self $model) {
            if (!$model->env_var) {
                $model->env_var = null;
            }
            if (!$model->config_name) {
                $model->config_name = null;
            }
        });
    }

    /**
     * Save costs when call multiple times.
     *
     * @return mixed|string
     */
    public function getValue()
    {
        return null === $this->value_actual ? $this->value_default : $this->value_actual;
    }

    /**
     * @return mixed|string
     * @deprecated 5.1.4
     */
    public function getValueAttribute()
    {
        return null === $this->value_actual ? $this->value_default : $this->value_actual;
    }

    public function isPrivate(): bool
    {
        return $this->is_public === 0;
    }
}

// end
