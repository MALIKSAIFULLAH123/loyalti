<?php

namespace MetaFox\Log\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Log\Database\Factories\LogMessageFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class LogMessage.
 *
 * @property int    $id
 * @property int    $level
 * @property string $level_name
 * @property string $timestamp
 * @property string $message
 * @property string $env
 * @property array  $context
 * @property array  $extra
 *
 * @method static LogMessageFactory factory(...$parameters)
 */
class LogMessage extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'log_message';

    protected $table = 'log_messages';

    public $timestamps = false;

    protected $guarded = [];

    /** @var string[] */
    protected $fillable = [
        'level',
        'level_name',
        'env',
        'message',
        'timestamp',
        'context',
        'extra',
    ];

    protected $casts = [
        'context' => AsArrayObject::class,
        'extra'   => AsArrayObject::class,
    ];

    /**
     * @return LogMessageFactory
     */
    protected static function newFactory()
    {
        return LogMessageFactory::new();
    }
}

// end
