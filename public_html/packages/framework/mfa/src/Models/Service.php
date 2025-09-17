<?php

namespace MetaFox\Mfa\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Service.
 *
 * @property int          $id
 * @property string       $name
 * @property string       $label
 * @property string       $service_class
 * @property int          $is_active
 * @property array<mixed> $config
 */
class Service extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'mfa_service';

    public const AUTHENTICATOR_SERVICE = 'authenticator';
    public const EMAIL_SERVICE         = 'email';
    public const SMS_SERVICE           = 'sms';

    protected $table = 'mfa_services';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'name',
        'label',
        'service_class',
        'is_active',
        'config',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'config' => 'array',
    ];
}

// end
