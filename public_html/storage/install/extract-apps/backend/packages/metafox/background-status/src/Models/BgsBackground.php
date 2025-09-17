<?php

namespace MetaFox\BackgroundStatus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\BackgroundStatus\Database\Factories\BgsBackgroundFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * Class BgsBackground.
 * @property        int                           $id
 * @property        int                           $collection_id
 * @property        string                        $image_path
 * @property        string                        $server_id
 * @property        int                           $is_deleted
 * @property        int                           $ordering
 * @property        int                           $view_only
 * @property        mixed                         $image_file_id
 * @property        string                        $created_at
 * @property        string                        $text_color
 * @property        BgsCollection                 $bgsCollection
 * @property        array<int|string, mixed>|null $images
 * @method   static BgsBackgroundFactory          factory(...$parameters)
 */
class BgsBackground extends Model implements Entity, HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'pstatusbg_background';
    public const IS_DELETED  = 1;

    protected $table = 'bgs_backgrounds';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'collection_id',
        'image_path',
        'server_id',
        'is_deleted',
        'ordering',
        'view_only',
        'text_color',
        'image_file_id',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    /**
     * @return BgsBackgroundFactory
     */
    protected static function newFactory(): BgsBackgroundFactory
    {
        return BgsBackgroundFactory::new();
    }

    public function bgsCollection(): BelongsTo
    {
        return $this->belongsTo(BgsCollection::class, 'collection_id', 'id');
    }

    public function getSizes(): array
    {
        return [48, 300, 1024];
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }
}
