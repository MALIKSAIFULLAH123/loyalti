<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Group\Database\Factories\CategoryFactory;
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
 * @property        int        $id
 * @property        string     $name
 * @property        string     $name_url
 * @property        int        $parent_id
 * @property        int        $is_active
 * @property        bool       $is_default
 * @property        int        $ordering
 * @property        string     $created_at
 * @property        string     $updated_at
 * @property        Category[] $subCategories
 * @property        Category   $parentCategory
 * @property        int        $total_item
 * @property        int        $level
 * @method   static CategoryFactory factory(...$parameters)
 */
class Category extends Model implements Entity, HasTotalItem, HasSubCategory
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use SoftDeletes;
    use TranslatableCategory;

    protected $table = 'group_categories';

    public const ENTITY_TYPE = 'group_category';

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

    public function subCategories(): ?HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'category_id', 'id');
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("group/search?category_id={$this->id}");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("group/search?category_id={$this->id}");
    }

    public function getIsDefaultAttribute(): bool
    {
        $categoryDefault = Settings::get('group.default_category');

        return $this->entityId() == $categoryDefault;
    }

    public function toSubCategoriesLink(): string
    {
        return sprintf('/group/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function toSubCategoriesUrl(): string
    {
        return sprintf('/group/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        if (!$this->parent_id) {
            return '/group/category/browse';
        }

        return sprintf('/group/category/%s/category/browse?parent_id=%s', $this->parent_id, $this->parent_id);
    }
}
