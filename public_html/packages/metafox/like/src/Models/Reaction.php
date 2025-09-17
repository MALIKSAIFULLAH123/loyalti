<?php

namespace MetaFox\Like\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MetaFox\Like\Database\Factories\ReactionFactory;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * Class Reaction.
 *
 * @property        string          $title
 * @property        string          $title_var
 * @property        string          $icon_path
 * @property        int             $icon_file_id
 * @property        int             $total_item
 * @property        string          $image_path
 * @property        string          $color
 * @property        string          $server_id
 * @property        string          $icon_font
 * @property        string|null     $icon
 * @property        string|null     $icon_mobile
 * @property        int             $ordering
 * @property        int             $is_active
 * @property        int             $is_default
 * @property        string          $created_at
 * @property        string          $updated_at
 * @property        Collection      $likes
 * @property        Collection      $items
 * @method   static ReactionFactory factory(...$parameters)
 *
 * @mixin Builder
 */
class Reaction extends Model implements Entity, HasThumbnail
{
    use HasFactory;
    use HasEntity;
    use HasThumbnailTrait;
    use HasTranslatableAttributes;

    protected $table = 'like_reactions';

    public const ENTITY_TYPE = 'preaction';

    public const IS_ACTIVE             = 1;
    public const IS_DEFAULT            = 1;
    public const LIMIT_ACTIVE_REACTION = 6;
    public const ICON_FONT_DEFAULT     = 'ico-thumbup-o';

    protected $fillable = [
        'title',
        'is_active',
        'icon_path',
        'icon_file_id',
        'image_path',
        'color',
        'server_id',
        'ordering',
        'is_default',
        'icon_font',
    ];

    protected $translatableAttributes = [
        'title',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'icon_file_id' => 'photo',
    ];

    protected static function newFactory(): ReactionFactory
    {
        return ReactionFactory::new();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'reaction_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LikeAgg::class, 'reaction_id', 'id');
    }

    public function getTitleAttribute(): string
    {
        return __p($this->title_var);
    }

    public function getTitleVarAttribute(): string
    {
        return Arr::get($this->attributes, 'title') ?: '';
    }

    public function getIconAttribute(): string
    {
        if (!empty($this->icon_path)) {
            return app('storage')->disk('asset')->url($this->icon_path);
        }

        if ($this->icon_file_id == null) {
            return '';
        }

        return app('storage')->getUrl($this->icon_file_id);
    }

    public function getIconMobileAttribute(): ?string
    {
        $iconPath = $this->icon;

        return Str::replace('.svg', '.png', $iconPath);
    }

    public function getThumbnail(): ?string
    {
        return $this->icon_file_id;
    }

    public function getImageAttribute(): ?string
    {
        $thumbnail = $this->getThumbnail();

        if (null === $thumbnail) {
            return null;
        }

        return app('storage')->getUrl($thumbnail);
    }

    public function getAdminEditUrlAttribute()
    {
        return sprintf('/like/reaction/edit/' . $this->id);
    }

    public function getAdminBrowseUrlAttribute()
    {
        return sprintf('/like/reaction/browse');
    }

    public function getTotalItemAttribute(): int
    {
        return $this->items()->sum('total_reaction');
    }
}
