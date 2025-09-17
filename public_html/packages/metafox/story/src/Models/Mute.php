<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Mute
 *
 * @property int    $id
 * @property string $expired_at
 */
class Mute extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'story_mute';

    protected $table = 'story_muted';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'expired_at',
    ];

}

// end
