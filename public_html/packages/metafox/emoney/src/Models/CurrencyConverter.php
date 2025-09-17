<?php

namespace MetaFox\EMoney\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\EMoney\Database\Factories\CurrencyConverterFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class CurrencyConverter.
 *
 * @property int    $id
 * @property string $service
 * @property string $service_class
 * @property string $title
 * @property string $description
 * @property string $config
 * @property string $link
 * @property bool   $is_default
 * @property string $created_at
 * @property string $updated_at
 */
class CurrencyConverter extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'ewallet_currency_converter';

    protected $table = 'emoney_currency_converters';

    /** @var string[] */
    protected $fillable = [
        'service',
        'service_class',
        'title',
        'description',
        'config',
        'link',
        'is_default',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'config'     => 'array',
        'is_default' => 'boolean',
    ];
}

// end
