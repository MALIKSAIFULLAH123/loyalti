<?php

namespace MetaFox\Queue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Queue\Database\Factories\FailedJobFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class FailedJob.
 *
 * @property int    $id
 * @property string $uuid
 * @property string $exception
 * @property string $queue
 * @property string $connection
 * @property string failed_at
 *
 * @method static FailedJobFactory factory(...$parameters)
 */
class FailedJob extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'failed_job';

    protected $table = 'failed_jobs';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];

    /**
     * @return FailedJobFactory
     */
    protected static function newFactory()
    {
        return FailedJobFactory::new();
    }
}

// end
