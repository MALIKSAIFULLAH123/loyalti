<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserVerifyErrorFactory;

/**
 * Class UserVerifyError.
 *
 * @property int                    $id
 * @method   static UserVerifyErrorFactory factory(...$parameters)
 */
class UserVerifyError extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_verify_error';

    protected $table = 'user_verify_error';

    /** @var string[] */
    protected $fillable = [
        'hash_code',
        'ip_address',
        'email',
        'updated_at',
        'created_at',
    ];

    /**
     * @return UserVerifyErrorFactory
     */
    protected static function newFactory()
    {
        return UserVerifyErrorFactory::new();
    }
}

// end
