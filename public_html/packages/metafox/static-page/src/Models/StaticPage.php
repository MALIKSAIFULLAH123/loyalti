<?php

namespace MetaFox\StaticPage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\StaticPage\Database\Factories\StaticPageFactory;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class StaticPage.
 *
 * @property        int                    $id
 * @property        string                 $slug
 * @property        string                 $title
 * @property        Collection             $contents
 * @property        StaticPageContent|null $masterContent
 * @property        StaticPageContent|null $content
 * @property        string                 $title_var
 * @method   static StaticPageFactory      factory(...$parameters)
 */
class StaticPage extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'static_page';

    protected $table = 'static_pages';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'module_id',
        'is_active',
        'is_phrase',
        'parse_php',
        'has_bookmark',
        'is_phrase',
        'full_size',
        'title',
        'slug',
        'disallow_access',
        'total_view',
        'total_comment',
        'total_share',
        'total_tag',
        'total_attachment',
        'created_at',
        'updated_at',
    ];

    protected $translatableAttributes = [
        'title',
    ];

    /**
     * @var string[]
     */
    protected $with = ['contents', 'content', 'masterContent'];

    /**
     * @return StaticPageFactory
     */
    protected static function newFactory()
    {
        return StaticPageFactory::new();
    }

    public function getTitleAttribute($value): string
    {
        return __p($value);
    }

    public function getTitleVarAttribute(): string
    {
        return Arr::get($this->attributes, 'title');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(StaticPageContent::class, 'static_page_id', 'id');
    }

    public function masterContent(): HasOne
    {
        return $this->hasOne(StaticPageContent::class, 'static_page_id', 'id')->where('locale', 'en');
    }

    public function content(): HasOne
    {
        $locale = app()->getLocale() ?: 'en';

        return $this->hasOne(StaticPageContent::class, 'static_page_id', 'id')->where('locale', $locale);
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("{$this->slug}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("{$this->slug}");
    }

    public function getDescriptionAttribute()
    {
        return substr($this->text, 0, 1000);
    }
}

// end
