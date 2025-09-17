<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserPrivacyResourceFactory;

/**
 * Class UserPrivacyResource.
 * @property int    $id
 * @property string $entity_type
 * @property string $type_id
 * @property string $phrase
 * @property int    $privacy_default
 */
class UserPrivacyResource extends Model
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_privacy_resource';

    protected $table = 'user_privacy_resources';

    /** @var string[] */
    protected $fillable = [
        'entity_type',
        'type_id',
        'phrase',
        'privacy_default',
    ];

    public $timestamps = false;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return UserPrivacyResourceFactory
     */
    public static function factory(array $parameters = []): UserPrivacyResourceFactory
    {
        return UserPrivacyResourceFactory::new($parameters);
    }
}

// end
