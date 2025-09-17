<?php

namespace MetaFox\Mfa\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Mfa\Database\Factories\UserServiceFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserService.
 *
 * @property        int                $id
 * @property        int                $user_id
 * @property        string             $user_type
 * @property        string             $service
 * @property        string             $value
 * @property        int                $is_active
 * @property        array<mixed>       $extra
 * @property        string             $last_authenticated
 * @property        string             $created_at
 * @property        string             $updated_at
 * @method   static UserServiceFactory factory(...$parameters)
 */
class UserService extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'mfa_user_service';

    protected $table = 'mfa_user_services';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'service',
        'value',
        'extra',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'extra' => 'array',
    ];

    /**
     * @return UserServiceFactory
     */
    protected static function newFactory()
    {
        return UserServiceFactory::new();
    }

    public function userVerifyCodes(): HasMany
    {
        return $this->hasMany(UserVerifyCode::class, 'service', 'service')
            ->where('user_id', $this->userId())
            ->where('user_type', $this->userType());
    }

    public function isActivated(): bool
    {
        return $this->is_active == 1;
    }

    public function onAuthenticated(): self
    {
        $this->last_authenticated = Carbon::now();
        $this->save();

        return $this;
    }

    public function onActivated(): self
    {
        $this->is_active = 1;
        $this->save();

        app('events')->dispatch('mfa.service_activated', [$this]);

        return $this;
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
            set: fn ($value) => Crypt::encrypt($value)
        );
    }

    protected function extra(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value, true),
            set: fn ($value) => Crypt::encrypt($value, true)
        );
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
