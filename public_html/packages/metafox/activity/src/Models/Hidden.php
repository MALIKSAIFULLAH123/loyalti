<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Database\Factories\HiddenFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;

/**
 * Class Hidden.
 *
 * @mixin Builder
 * @property        int           $id
 * @property        int           $user_id
 * @property        string        $user_type
 * @property        int           $feed_id
 * @property        string        $created_at
 * @property        string        $updated_at
 * @property        UserEntity    $userEntity
 * @method   static HiddenFactory factory()
 */
class Hidden extends Model
{
    use HasFactory;
    use HasEntity;
    use HasUserMorph;

    protected $table = 'activity_hidden';

    public const ENTITY_TYPE = 'feed_hidden';

    public const IMPORT_ENTITY_TYPE = 'activity_hidden';

    protected $fillable = ['user_id', 'user_type', 'feed_id'];

    protected static function newFactory(): HiddenFactory
    {
        return HiddenFactory::new();
    }
}
