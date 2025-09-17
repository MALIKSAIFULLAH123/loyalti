<?php

namespace MetaFox\Layout\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\App\Models\Package;
use MetaFox\Layout\Database\Factories\ThemeFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Theme.
 *
 * @property        int          $id
 * @method   static ThemeFactory factory(...$parameters)
 * @property        int          $total_variant
 * @property        string       $theme_id
 * @property        string       $title
 * @property        bool         $is_active
 * @property        bool         $default_variant_id
 * @property        string       $module_id
 * @property        string       $package_id
 * @property        ?Package     $package
 * @property        string       $created_at
 * @property        string       $updated_at
 * @property        string       $resolution
 * @property        bool         $is_system
 * @property        ?string      $imageUrl
 * @property        int          $is_default
 */
class Theme extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'layout_theme';

    protected $table = 'layout_themes';

    /** @var string[] */
    protected $fillable = [
        'theme_id',
        'default_variant_id', // default falvor id
        'title',
        'total_variant',
        'module_id',
        'package_id',
        'is_system',
        'is_active',
        'resolution',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function package()
    {
        return $this->hasOne(Package::class, 'package_id', 'name');
    }

    public function getImageUrlAttribute(): ?string
    {
        /** @var Variant $variant */
        $variant  = Variant::query()
            ->firstWhere('theme_id', '=', $this->theme_id);

        if ($variant) {
            return $variant->imageUrl;
        }

        return null;
    }

    public function getIsDefaultAttribute()
    {
        $current = $this->resolution === 'admin' ? config('app.mfox_admincp_theme', 'admincp:admincp') : config('app.mfox_site_theme', 'a0:a0');

        return str_starts_with($current, "$this->theme_id:");
    }

    /**
     * @return ThemeFactory
     */
    protected static function newFactory()
    {
        return ThemeFactory::new();
    }
}

// end
