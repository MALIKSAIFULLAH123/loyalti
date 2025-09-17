<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\User as SocialUser;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\SocialAccountFactory;

/**
 * Class SocialAccount.
 *
 * @mixin Builder
 * @property int                  $id
 * @property string               $provider_user_id
 * @property string               $provider
 * @property int                  $user_id
 * @property string               $created_at
 * @property string               $updated_at
 * @property User                 $user
 * @property string               $hash
 * @property string               $hash_expired_at
 * @property array                $extra
 * @property SocialUser           $social_user
 * @property array                $request_params
 * @method   SocialAccountFactory factory()
 */
class SocialAccount extends Model
{
    use HasEntity;
    use HasFactory;

    public const FACEBOOK         = 'facebook';
    public const HASH_EXPIRE_TIME = 5;

    public const ENTITY_TYPE = 'social_account';

    protected $table = 'social_accounts';

    /** @var string[] */
    protected $fillable = [
        'provider_user_id',
        'provider',
        'user_id',
        'created_at',
        'updated_at',
        'social_user',
        'hash',
        'extra',
        'hash_expired_at',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    protected static function newFactory(): SocialAccountFactory
    {
        return SocialAccountFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    public function getSocialUserAttribute(): ?SocialUser
    {
        $socialUser = Arr::get($this->extra, 'social_user');

        if (empty($socialUser)) {
            return null;
        }

        return unserialize($socialUser);
    }

    public function getRequestParamsAttribute(): array
    {
        return Arr::get($this->extra, 'request_params', []);
    }

    public function isRegister(): bool
    {
        return Arr::get($this->extra, 'is_register', false);
    }
}

// end
