<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Marketplace\Database\Factories\ImageFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * Class Image.
 *
 * @property int $id
 * @property int $listing_id
 * @property int $image_file_id
 * @property int $ordering
 *
 * @method ImageFactory static ImageFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @mixin Builder
 */
class Image extends Model implements
    Entity,
    HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'marketplace_image';

    protected $table = 'marketplace_listing_images';

    protected $fillable = [
        'listing_id',
        'image_file_id',
        'ordering',
    ];

    public $timestamps = false;

    protected static function newFactory(): ImageFactory
    {
        return ImageFactory::new();
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }
}
