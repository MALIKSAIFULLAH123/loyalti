<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserPasswordFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserPassword.
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $password_hash
 * @property int    $password_salt
 * @property int    $password_method
 * @method   static UserPasswordFactory factory(...$parameters)
 */
class UserPassword extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_password';

    protected $table = 'user_passwords';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'user_id';

    // store alternate password import from another source code.
    /** @var string[] */
    protected $fillable = [
        'user_id',
        'password_hash', //
        'password_salt',
        'password_method', // MetaFox\User\Password\v4Password
        'params'
    ];

    /**
     * @return UserPasswordFactory
     */
    protected static function newFactory()
    {
        return UserPasswordFactory::new();
    }

    /**
     * @param  ?string $input
     * @return void
     */
    public function validateForPassportPasswordGrant(?string $input): bool
    {
        if (!$this->password_method) {
            return false;
        }
        return resolve($this->password_method)
            ->check($input, (string)$this->password_hash, (string)$this->password_salt);
    }
}

// end
