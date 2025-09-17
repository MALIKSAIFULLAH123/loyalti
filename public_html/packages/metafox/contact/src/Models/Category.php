<?php

namespace MetaFox\Contact\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Localize\Support\Traits\TranslatableCategory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasSubCategory;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Models/Category.stub.
 */

/**
 * Class Category.
 *
 * @mixin Builder
 * @property int      $id
 * @property string   $name
 * @property string   $name_url
 * @property bool     $is_default
 * @property bool     $is_active
 * @property int      $ordering
 * @property int      $parent_id
 * @property int      $total_item
 * @property int      $level
 * @property Category $subCategories
 * @property Category $parentCategory
 * @property string   $created_at
 * @property string   $updated_at
 */
class Category extends Model implements Entity, HasSubCategory
{
    use HasEntity;
    use HasFactory;
    use SoftDeletes;
    use TranslatableCategory;

    public const ENTITY_TYPE = 'contact_category';

    protected $table = 'contact_categories';

    public const IS_ACTIVE = 1;

    protected $fillable = [
        'name',
        'is_active',
        'ordering',
        'parent_id',
        'name_url',
        'total_item',
        'level',
        'ordering',
    ];

    public function subCategories(): HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function parentCategory(): BelongsTo
    {
        $relation = $this->belongsTo(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("/contact/search?category_id={$this->id}");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("/contact/search?category_id={$this->id}");
    }

    public function toSubCategoriesLink(): string
    {
        return sprintf('/contact/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function toSubCategoriesUrl(): string
    {
        return sprintf('/contact/category/%s/category/browse?parent_id=%s', $this->id, $this->id);
    }

    public function getAdminBrowseUrlAttribute()
    {
        if (!$this->parent_id) {
            return '/contact/category/browse';
        }

        return sprintf('/contact/category/%s/category/browse?parent_id=%s', $this->parent_id, $this->parent_id);
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getIsDefaultAttribute(): bool
    {
        $categoryDefault = Settings::get('contact.default_category');

        return $this->entityId() == $categoryDefault;
    }
}
