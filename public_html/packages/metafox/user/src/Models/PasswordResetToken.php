<?php

namespace MetaFox\User\Models;

use Illuminate\Support\Facades\URL;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\PasswordResetTokenFactory;

/**
 * Class PasswordResetToken.
 *
 * @property        int                       $id
 * @property        string                    $value
 * @property        string                    $expired_at
 * @property        string                    $created_at
 * @property        string                    $updated_at
 * @property        string                    $password_form_link
 * @method   static PasswordResetTokenFactory factory(...$parameters)
 */
class PasswordResetToken extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'password_reset_token';

    protected $table = 'user_reset_password_token';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'value',
        'expired_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'password_form_link',
    ];

    /**
     * @return PasswordResetTokenFactory
     */
    protected static function newFactory(): PasswordResetTokenFactory
    {
        return PasswordResetTokenFactory::new();
    }

    public function getPasswordFormLinkAttribute(): string
    {
        $baseLink = url_utility()->makeApiFullUrl('user/password/reset?user_id=%s&token=%s');
        $url      = sprintf($baseLink, $this->userId(), $this->value);

        return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', $url, $url);
    }
}

// end
