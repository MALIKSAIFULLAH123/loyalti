<?php

namespace MetaFox\Sticker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Sticker\Database\Factories\StickerSetFactory;
use MetaFox\User\Models\UserEntity;

/**
 * Class StickerSet.
 * @property        int        $id
 * @property        string     $title
 * @property        int        $is_default
 * @property        int        $is_active
 * @property        int        $thumbnail_id
 * @property        int        $ordering
 * @property        int        $view_only
 * @property        int        $is_deleted
 * @property        int        $total_sticker
 * @property        Collection $stickers
 * @property        Sticker    $thumbnail
 * @method   static StickerSetFactory factory(...$parameters)
 */
class StickerSet extends Model implements Entity, HasAmounts, HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasNestedAttributes;
    use HasAmountsTrait;
    use HasThumbnailTrait;

    public const IS_VIEW_ONLY = 1;
    public const IS_DELETED = 1;
    public const IS_ACTIVE = 1;
    public const IS_DEFAULT = 1;
    public const MAX_DEFAULT = 2;
    public const DEFAULT_ITEM_PER_PAGE = 5;

    public const ENTITY_TYPE = 'sticker_set';

    protected $table = 'sticker_sets';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'title',
        'used',
        'is_default',
        'is_active',
        'thumbnail_id',
        'ordering',
        'view_only',
        'is_deleted',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'stickers',
    ];

    /**
     * @return StickerSetFactory
     */
    protected static function newFactory(): StickerSetFactory
    {
        return StickerSetFactory::new();
    }

    public function stickers(): HasMany
    {
        return $this->hasMany(Sticker::class, 'set_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserEntity::class,
            'sticker_user_values',
            'set_id',
            'user_id'
        );
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Sticker::class, 'thumbnail_id', 'id');
    }

    public function getThumbnail(): ?string
    {
        $thumbnail = $this->thumbnail;

        if (null == $thumbnail) {
            return null;
        }

        return $thumbnail->image_file_id;
    }

    public function getAdminEditUrlAttribute()
    {
        return sprintf('/sticker/sticker-set/edit/' . $this->id);
    }

    public function getAdminBrowseUrlAttribute()
    {
        return sprintf('/sticker/sticker-set/browse');
    }

    public function getAvatarAttribute(): ?string
    {
        return app('storage')->getUrl($this->getThumbnail());
    }

    public function getThumbsAttribute(): ?array
    {
        return $this->thumbnail->thumbs;
    }

}

// end
