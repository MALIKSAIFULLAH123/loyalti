<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\UserVerifyFactory;

/**
 * Class UserVerify.
 *
 * @property int     $id
 * @property string  $user_type
 * @property string  $user_id
 * @property ?string $expires_at
 * @property ?string $action
 * @property string  $hash_code
 * @property ?string $verifiable
 *
 * @method static UserVerifyFactory factory(...$parameters)
 */
class UserVerify extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE         = 'user_verify';
    public const ACTION_EMAIL        = 'verify_email';
    public const ACTION_PHONE_NUMBER = 'verify_phone_number';

    protected $table = 'user_verify';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'verifiable',
        'hash_code',
        'action',
        'expires_at',
        'user_id',
        'user_type',
        'is_verified',
    ];

    /**
     * @return UserVerifyFactory
     */
    protected static function newFactory()
    {
        return UserVerifyFactory::new();
    }

    public function maskAsExpired()
    {
        $this->fill([
            'expired_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function markAsVerified()
    {
        $this->fill([
            'is_verified' => 1,
        ])->save();
    }
}

// end
