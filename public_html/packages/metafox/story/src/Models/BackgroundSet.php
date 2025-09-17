<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class BackgroundSet.
 *
 * @property int             $id
 * @property Collection      $backgrounds
 * @property string          $title
 * @property int             $is_default
 * @property int             $is_active
 * @property int             $thumbnail_id
 * @property int             $ordering
 * @property int             $view_only
 * @property int             $is_deleted
 * @property int             $total_background
 * @property int             $main_background_id
 * @property StoryBackground $mainBackground
 * @mixin Builder
 */
class BackgroundSet extends Model implements Entity, HasAmounts, HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'story_background_set';

    protected $table = 'story_background_set';

    /** @var string[] */
    protected $fillable = [
        'title',
        'used',
        'is_default',
        'is_active',
        'main_background_id',
        'total_background',
        'ordering',
        'view_only',
        'is_deleted',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'backgrounds',
    ];

    public function backgrounds(): HasMany
    {
        return $this->hasMany(StoryBackground::class, 'set_id', 'id')
            ->orderBy('ordering');
    }

    public function mainBackground(): BelongsTo
    {
        return $this->belongsTo(StoryBackground::class, 'main_background_id', 'id');
    }

    public function getThumbnail(): ?string
    {
        $thumbnail = $this->mainBackground;

        return $thumbnail?->image_file_id;
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '/story/background-set/edit/' . $this->id;
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '/story/background-set/browse';
    }

    public function getAvatarAttribute(): ?string
    {
        return app('storage')->getUrl($this->getThumbnail());
    }
}

// end
