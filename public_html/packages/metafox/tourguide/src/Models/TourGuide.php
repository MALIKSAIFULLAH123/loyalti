<?php

namespace MetaFox\TourGuide\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\TourGuide\Supports\Constants;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class TourGuide.
 *
 * @property int        $id
 * @property string     $name
 * @property string     $url
 * @property string     $page_name
 * @property string     $privacy
 * @property bool       $is_auto
 * @property bool       $is_active
 * @property string     $created_at
 * @property string     $updated_at
 * @property Collection $activeSteps
 * @property Collection $hidden
 */
class TourGuide extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'tour_guide';

    protected $table = 'tour_guides';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'url',
        'page_name',
        'privacy',
        'is_auto',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_auto'   => 'boolean',
        'is_active' => 'boolean',
    ];

    public function isMemberPrivacy(): bool
    {
        return $this->privacy == Constants::MEMBER;
    }

    public function isGuestPrivacy(): bool
    {
        return $this->privacy == Constants::GUEST;
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class, 'tour_guide_id', 'id');
    }

    public function hidden(): HasMany
    {
        return $this->hasMany(Hidden::class, 'tour_guide_id', 'id');
    }

    public function activeSteps(): HasMany
    {
        return $this->hasMany(Step::class, 'tour_guide_id', 'id')
            ->where('tour_guide_steps.is_active', true)
            ->orderBy('ordering');
    }
}
