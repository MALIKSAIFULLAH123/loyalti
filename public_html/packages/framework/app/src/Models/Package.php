<?php

namespace MetaFox\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MetaFox\App\Database\Factories\PackageFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Module.
 * @property int      $id
 * @property int      $priority
 * @property string   $icon
 * @property string   $name
 * @property string   $type
 * @property string   $category
 * @property string   $title
 * @property string   $label
 * @property string   $path
 * @property string   $alias
 * @property string   $version
 * @property string   $latest_version
 * @property string   $description
 * @property string   $keywords
 * @property string   $namespace
 * @property string[] $frontend
 * @property string[] $mobile
 * @property string   $name_studly
 * @property bool     $is_active
 * @property bool     $is_bundled
 * @property bool     $is_installed
 * @property array    $aliases
 * @property bool     $is_purchased
 * @property string   $order
 * @property string   $is_core
 * @property string   $author
 * @property string   $author_url
 * @property string   $internal_url
 * @property string   $expired_at
 * @property bool     $purchased_at
 * @property string   $bundle_status
 * @property string   $internal_admin_url
 * @property array    $providers
 * @property ?string  $store_url
 * @property ?int     $store_id
 * @property string   $created_at
 * @property string   $updated_at
 * @mixin Builder
 */
class Package extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    /** @var string */
    public const ENTITY_TYPE = 'package';

    /** @var string */
    protected $table = 'packages';

    /** @var string[] */
    protected $fillable = [
        'name',
        'icon',
        'title',
        'author',
        'author_url',
        'store_id',
        'store_url',
        'path',
        'namespace',
        'name_studly',
        'alias',
        'version',
        'providers',
        'type',
        'category',
        'latest_version',
        'keywords',
        'description',
        'requires',
        'is_active',
        'is_installed',
        'is_bundled',
        'order',
        'is_core',
        'frontend',
        'mobile',
        'internal_url',
        'internal_admin_url',
        'bundle_status',
        'created_at',
        'updated_at',
        'expired_at',
        'priority',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_core'   => 'boolean',
        'providers' => 'array',
        'requires'  => 'array',
        'frontend'  => 'array',
        'mobile'    => 'array',
        'aliases'   => 'array',
    ];

    /**
     * @return PackageFactory
     */
    protected static function newFactory(): PackageFactory
    {
        return PackageFactory::new();
    }

    /**
     * @param  array<string, mixed> $composer
     * @return void
     */
    public function updateComposer(array &$composer): void
    {
        Arr::set($composer, 'name', $this->name);
        Arr::set($composer, 'description', $this->description);
        Arr::set($composer, 'keyword', $this->keywords);
        Arr::set($composer, 'version', $this->version);
        Arr::set($composer, 'extra.metafox.core', (bool) $this->is_core);
        Arr::set($composer, 'authors.0.name', $this->author);
        Arr::set($composer, 'authors.0.homepage', $this->author_url);
        Arr::set($composer, 'extra.metafox.frontend', $this->frontend);
        Arr::set($composer, 'extra.metafox.type', $this->type);
        Arr::set($composer, 'extra.metafox.mobile', $this->mobile);
        Arr::set($composer, 'extra.metafox.aliases', $this->aliases);
        Arr::set($composer, 'extra.metafox.priority', $this->priority);
        Arr::set($composer, 'extra.metafox.store_id', $this->store_id);
        Arr::set($composer, 'extra.metafox.store_url', $this->store_url);
        Arr::set($composer, 'extra.metafox.path', $this->path);
        Arr::set($composer, 'extra.metafox.alias', $this->alias);
        Arr::set($composer, 'extra.metafox.providers', $this->providers);
        Arr::set($composer, 'extra.metafox.internalUrl', $this->internal_url);
        Arr::set($composer, 'extra.metafox.internalAdminUrl', $this->internal_admin_url);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(config('permission.models.permission'), 'module_id', 'alias');
    }

    public function getTitleAttribute(string $value): string
    {
        $key   = "{$this->alias}::phrase.app_name";
        $title = __p($key);

        if ($key !== $title) {
            return $title;
        }

        return $value;
    }

    public function getLabelAttribute(): string
    {
        if ($this->alias === 'platform') {
            return __p('core::phrase.platform_label_menu_s');
        }

        $key   = "{$this->alias}::phrase.label_menu_s";
        $label = __p($key);

        if ($label !== $key) {
            return $label;
        }

        return $this->title;
    }
}

// end
