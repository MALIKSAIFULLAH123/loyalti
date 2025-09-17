<?php

namespace MetaFox\Mfa\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Mfa\Database\Factories\UserAuthTokenFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserAuthToken.
 *
 * @property        int    $id
 * @property        int    $user_id
 * @property        string $user_type
 * @property        string $value
 * @property        int    $is_authenticated
 * @property        string $expired_at
 * @property        string $resolution
 * @method   static UserAuthTokenFactory factory(...$parameters)
 */
class UserAuthToken extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'mfa_user_auth_token';

    protected $table = 'mfa_user_auth_tokens';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'value',
        'is_authenticated',
        'expired_at',
        'resolution',
    ];

    /**
     * @return UserAuthTokenFactory
     */
    protected static function newFactory()
    {
        return UserAuthTokenFactory::new();
    }

    public function isExpired(): bool
    {
        return empty($this->expired_at) || Carbon::now()->gt($this->expired_at);
    }

    public function isAuthenticated(): bool
    {
        return $this->is_authenticated == 1;
    }

    public function onAuthenticated(): self
    {
        $this->is_authenticated = 1;
        $this->save();

        return $this;
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }
}

// end
