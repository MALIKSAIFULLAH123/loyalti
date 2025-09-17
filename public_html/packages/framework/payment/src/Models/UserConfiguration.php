<?php

namespace MetaFox\Payment\Models;

use Illuminate\Support\Arr;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Payment\Database\Factories\UserConfigurationFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserConfiguration.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $gateway_id
 * @property string $value
 */
class UserConfiguration extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'payment_user_configuration';

    protected $table = 'payment_user_configurations';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'gateway_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function getValueAttribute(): ?array
    {
        $value = Arr::get($this->attributes, 'value');

        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return null;
        }

        if (!count($value)) {
            return null;
        }

        return $value;
    }
}
