<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class PrivacyStream.
 *
 * @property int $id
 * @property int $privacy_id
 * @property int $item_id
 */
class PrivacyStream extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'story_privacy_streams';

    protected $table = 'story_privacy_streams';
    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
