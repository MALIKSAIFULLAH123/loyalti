<?php

namespace MetaFox\Mfa\Models;

use Carbon\Carbon;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserVerifyCode.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $service
 * @property string $action
 * @property string $code
 * @property string $expired_at
 * @property int    $is_active
 * @property string $authenticated_at
 * @property string $last_sent_at
 */
class UserVerifyCode extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'mfa_user_verify_code';

    public const SETUP_ACTION = 'setup';
    public const AUTH_ACTION  = 'auth';

    protected $table = 'mfa_user_verify_code';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'service',
        'action',
        'code',
        'last_sent_at',
        'authenticated_at',
        'is_active',
        'expired_at',
    ];

    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->expired_at);
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated_at !== null;
    }

    public function onAuthenticated(): self
    {
        $this->authenticated_at = Carbon::now();
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
