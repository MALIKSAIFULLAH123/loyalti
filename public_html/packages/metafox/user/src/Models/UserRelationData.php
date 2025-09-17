<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserRelationDataFactory;

/**
 * Class UserRelationData.
 *
 * @property        int                     $id
 * @method   static UserRelationDataFactory factory(...$parameters)
 */
class UserRelationData extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_relation_data';

    protected $table = 'user_relation_data';

    /** @var string[] */
    protected $fillable = [
        'relation_id',
        'user_id',
        'user_type',
        'with_user_id',
        'with_user_type',
        'status_id',
        'total_like',
        'total_comment',
        'updated_at',
        'created_at',
    ];

    /**
     * @return UserRelationDataFactory
     */
    protected static function newFactory()
    {
        return UserRelationDataFactory::new();
    }
}

// end
