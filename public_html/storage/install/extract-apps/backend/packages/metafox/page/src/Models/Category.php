<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Localize\Support\Traits\TranslatableCategory;
use MetaFox\Page\Database\Factories\PageCategoryFactory;
use MetaFox\Platform\Contracts\HasSubCategory;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Category.
 * @property        int        $id
 * @property        string     $name
 * @property        string     $name_url
 * @property        int        $is_active
 * @property        bool       $is_default
 * @property        int        $ordering
 * @property        string     $created_at
 * @property        string     $updated_at
 * @property        int        $total_item
 * @property        Category[] $subCategories
 * @property        Category   $parentCategory
 * @property        int        $parent_id
 * @property        int        $level
 * @method   static PageCategoryFactory factory()
 */
class Category extends Model implements HasTotalItem, HasSubCategory
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use SoftDeletes;
    use TranslatableCategory;

    public const ENTITY_TYPE = 'page_category';

    public const IS_ACTIVE = 1;

    protected $table = 'page_categories';

    protected $primaryKey = 'id';

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

    protected static function newFactory(): PageCategoryFactory
    {
        return PageCategoryFactory::new();
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'category_id', 'id');
    }

    public function subCategories(): HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("page/search?category_id={$this->id}");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("page/search?category_id={$this->id}");
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function getIsDefaultAttribute(): bool
    {
        $categoryDefault = Settings::get('page.default_category');

        return $this->entityId() == $categoryDefault;
    }

    public function toSubCategoriesLink(): string
    {
        return url_utility()->makeApiUrl(sprintf(
            '/page/category/%s/category/browse?parent_id=%s',
            $this->entityId(),
            $this->entityId()
        ));
    }

    public function toSubCategoriesUrl(): string
    {
        return url_utility()->makeApiFullUrl(sprintf(
            '/page/category/%s/category/browse?parent_id=%s',
            $this->entityId(),
            $this->entityId()
        ));
    }

    public function getAdminBrowseUrlAttribute()
    {
        if (!$this->parent_id) {
            return '/page/category/browse';
        }

        return sprintf('/page/category/%s/category/browse?parent_id=%s', $this->parent_id, $this->parent_id);
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }
}
