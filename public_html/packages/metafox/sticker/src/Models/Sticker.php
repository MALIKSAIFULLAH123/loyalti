<?php

namespace MetaFox\Sticker\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Sticker\Database\Factories\StickerFactory;

/**
 * Class Sticker.
 *
 * @mixin Builder
 * @property        int                      $id
 * @property        int                      $set_id
 * @property        string                   $image_file_id
 * @property        int                      $ordering
 * @property        int                      $view_only
 * @property        int                      $is_deleted
 * @property        int                      $thumbnail_file_id
 * @property        StickerSet               $stickerSet
 * @property        string                   $image_path
 * @property        array<int|string, mixed> $images
 * @property        array<int|string, mixed> $thumbs
 * @method   static StickerFactory           factory(...$parameters)
 */
class Sticker extends Model implements Entity, HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasThumbnailTrait;

    public const IS_DELETED   = 1;
    public const IS_VIEW_ONLY = 1;

    public const ENTITY_TYPE = 'sticker';

    protected $table = 'stickers';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'set_id',
        'image_path',
        'server_id',
        'ordering',
        'view_only',
        'image_file_id',
        'thumbnail_file_id',
        'is_deleted',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id'     => 'photo',
        'thumbnail_file_id' => 'photo',
    ];

    /**
     * @return StickerFactory
     */
    protected static function newFactory(): StickerFactory
    {
        return StickerFactory::new();
    }

    public function stickerSet(): BelongsTo
    {
        return $this->belongsTo(StickerSet::class, 'set_id', 'id');
    }

    public function getSizes(): array
    {
        return [50, 150, 200];
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function getThumbnailFile(): ?int
    {
        return $this->thumbnail_file_id;
    }

    public function getThumbsAttribute(): ?array
    {
        return app('storage')->getUrls($this->getThumbnailFile());
    }

    public function getImageAttribute(): ?string
    {
        return app('storage')->getUrl($this->getThumbnail());
    }
}

// end
