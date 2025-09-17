<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Foxexpert\Sevent\Database\Factories\CategoryFactory;
use MetaFox\Localize\Support\Traits\TranslatableCategory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasSubCategory;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Category.
 * @mixin Builder
 * @property        int      $id
 * @property        string   $name
 * @property        string   $name_url
 * @property        bool     $is_active
 * @property        bool     $is_default
 * @property        int      $ordering
 * @property        int      $parent_id
 * @property        int      $total_item
 * @property        Category $subCategories
 * @property        Category $parentCategory
 * @property        string   $created_at
 * @property        string   $updated_at
 * @property        int      $level
 * @method   static CategoryFactory factory()
 */
class Category extends Model implements
    Entity,
    HasTotalItem,
    HasSubCategory
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use SoftDeletes;
    use TranslatableCategory;

    public const ENTITY_TYPE = 'sevent_category';

    protected $table = 'sevent_categories';

    public const IS_ACTIVE = 1;

    protected $fillable = [
        'name',
        'is_active',
        'ordering',
        'parent_id',
        'name_url',
        'total_item',
        'level',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    public function subCategories(): HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function sevents(): BelongsToMany
    {
        return $this->belongsToMany(
            Sevent::class,
            'sevent_category_data',
            'category_id',
            'item_id'
        )
            ->using(CategoryData::class);
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("/sevent?category_id={$this->id}");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("/sevent?category_id={$this->id}");
    }

    public function getIsDefaultAttribute(): bool
    {
        $categoryDefault = Settings::get('sevent.default_category');

        return $this->entityId() == $categoryDefault;
    }

    public function toSubCategoriesLink(): string
    {
        return sprintf('/sevent/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function toSubCategoriesUrl(): string
    {
        return sprintf('/sevent/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getAdminBrowseUrlAttribute()
    {
        if (!$this->parent_id) {
            return '/sevent/category/browse';
        }

        return sprintf('/sevent/category/%s/category/browse?parent_id=%s', $this->parent_id, $this->parent_id);
    }
}
