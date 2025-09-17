<?php

namespace MetaFox\Importer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Importer\Database\Factories\LogFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Log.
 *
 * @property int $id
 * @property int $level_name
 * @property int $env
 * @property int $message
 * @property int $created_at
 * @method   static LogFactory factory(...$parameters)
 */
class Log extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'importer_log';

    protected $table = 'importer_logs';

    /** @var string[] */
    protected $fillable = [];

    /**
     * @return LogFactory
     */
    protected static function newFactory()
    {
        return LogFactory::new();
    }
}

// end
