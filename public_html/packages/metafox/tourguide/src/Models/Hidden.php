<?php

namespace MetaFox\TourGuide\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Hidden.
 *
 * @property int $id
 * @property int $tour_guide_id
 */
class Hidden extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'tour_guide_hidden';

    protected $table = 'tour_guide_hidden';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'tour_guide_id',
        'user_id',
    ];

    public function tourGuide(): BelongsTo
    {
        return $this->belongsTo(TourGuide::class, 'tour_guide_id', 'id');
    }
}
