<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Story\Database\Factories\StoryBackgroundFactory;
use MetaFox\Story\Support\StorySupport;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class StoryBackground.
 *
 * @property        int                    $id
 * @property        int                    $image_file_id
 * @property        int                    $server_id
 * @property        int                    $view_only
 * @property        int                    $is_deleted
 * @property        int                    $total_item
 * @property        int                    $ordering
 * @property        string                 $icon_path
 * @property        string                 $image_path
 * @property        string                 $created_at
 * @property        string                 $updated_at
 * @property        BackgroundSet          $backgroundSet
 * @method   static StoryBackgroundFactory factory(...$parameters)
 */
class StoryBackground extends Model implements Entity, HasThumbnail, HasTotalItem
{
    use HasEntity;
    use HasFactory;
    use HasThumbnailTrait;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'story_background';

    protected $table = 'story_backgrounds';

    /** @var string[] */
    protected $fillable = [
        'set_id',
        'image_file_id',
        'icon_path',
        'image_path',
        'server_id',
        'view_only',
        'total_item',
        'is_deleted',
        'ordering',
        'created_at',
        'updated_at',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    /**
     * @return StoryBackgroundFactory
     */
    protected static function newFactory()
    {
        return StoryBackgroundFactory::new();
    }

    public function backgroundSet(): BelongsTo
    {
        return $this->belongsTo(BackgroundSet::class, 'set_id', 'id');
    }

    public function getSizes(): array
    {
        return StorySupport::RESIZE_IMAGE;
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function getImageAttribute(): ?string
    {
        return Storage::disk('asset')->url($this->image_path);
    }
}

// end
