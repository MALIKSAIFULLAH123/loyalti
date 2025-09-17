<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class PlacementText.
 *
 * @property int $id
 */
class PlacementText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'advertise_placement_text';

    protected $table = 'advertise_placement_text';

    /** @var string[] */
    protected $fillable = [
        'text',
        'text_parsed',
    ];

    public $timestamps = false;

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Placement::class, 'id');
    }
}

// end
