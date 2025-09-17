<?php

namespace MetaFox\Forum\Models;

use MetaFox\Forum\Notifications\AddModerator;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Moderator.
 *
 * @property int $id
 */
class Moderator extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'forum_moderator';

    protected $table = 'forum_moderator';

    /** @var string[] */
    protected $fillable = [
        'forum_id',
        'user_id',
        'user_type',
    ];

    public $timestamps = false;

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'id');
    }

    public function toAddModeratorNotification(): array
    {
        return [$this->user, new AddModerator($this)];
    }
}

// end
