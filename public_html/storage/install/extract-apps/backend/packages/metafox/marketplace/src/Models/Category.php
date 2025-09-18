<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Localize\Support\Traits\TranslatableCategory;
use MetaFox\Marketplace\Database\Factories\CategoryFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasSubCategory;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Category.
 * @mixin Builder
 * @property        int      $id
 * @property        int      $parent_id
 * @property        string   $name
 * @property        string   $name_url
 * @property        int      $is_active
 * @property        bool     $is_default
 * @property        int      $ordering
 * @property        int      $total_item
 * @property        int      $level
 * @property        string   $created_at
 * @property        string   $updated_at
 * @property        Category $subCategories
 * @property        Category $parentCategory
 * @method   static CategoryFactory factory()
 */
class Category extends Model implements Entity, HasTotalItem, HasUrl, HasSubCategory
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use SoftDeletes;
    use TranslatableCategory;

    public const ENTITY_TYPE = 'marketplace_category';

    protected $table = 'marketplace_categories';

    public const IS_ACTIVE = 1;

    protected $fillable = [
        'name',
        'name_url',
        'parent_id',
        'is_active',
        'ordering',
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

    public function marketplaces(): BelongsToMany
    {
        return $this->belongsToMany(
            Listing::class,
            'marketplace_category_data',
            'category_id',
            'item_id'
        )->using(CategoryData::class);
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('marketplace/search?category_id=' . $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('marketplace/search?category_id=' . $this->entityId());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('marketplace/search?category_id=' . $this->entityId());
    }

    public function getIsDefaultAttribute(): bool
    {
        $categoryDefault = Settings::get('marketplace.default_category');

        return $this->entityId() == $categoryDefault;
    }

    public function toSubCategoriesLink(): string
    {
        return url_utility()->makeApiUrl(sprintf(
            '/marketplace/category/%s/category/browse?parent_id=%s',
            $this->entityId(),
            $this->entityId()
        ));
    }

    public function toSubCategoriesUrl(): string
    {
        return url_utility()->makeApiFullUrl(sprintf(
            '/marketplace/category/%s/category/browse?parent_id=%s',
            $this->entityId(),
            $this->entityId()
        ));
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        if (!$this->parent_id) {
            return '/marketplace/category/browse';
        }

        return sprintf(
            '/marketplace/category/%s/category/browse?parent_id=%s',
            $this->parent_id,
            $this->parent_id
        );
    }
}
