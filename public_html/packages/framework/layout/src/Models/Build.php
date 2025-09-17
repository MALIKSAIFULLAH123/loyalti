<?php

namespace MetaFox\Layout\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Layout\Database\Factories\BuildFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Build.
 *
 * @property int    $id
 * @method   static BuildFactory factory(...$parameters)
 * @property string $job_id
 * @property string $reason
 * @property string $data
 * @property string $result
 * @property string $bundle_url
 * @property string $log_url
 * @property string $bundle_status
 * @property string $bundle_result
 * @property string $bundle_disk
 * @property string $bundle_path
 * @property string $created_at
 * @property string $updated_at
 */
class Build extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const EXPIRE_MINUTE = 30;

    public const ENTITY_TYPE = 'core_bundle_task';
    /**
     * @var mixed|string
     */
    protected $table = 'bundle_tasks';

    /** @var string[] */
    protected $fillable = [
        'job_id',
        'reason',
        'data',
        'result',
        'bundle_url',
        'log_url',
        'bundle_status',
        'bundle_disk',
        'bundle_path',
    ];

    /**
     * @return BuildFactory
     */
    protected static function newFactory()
    {
        return BuildFactory::new();
    }

    public function running(): bool
    {
        return in_array(
            $this->bundle_status,
            ['sending', 'pending', 'processing', 'downloading', 'downloaded', 'extracting']
        );
    }

    // task has been execute overlap 30 minus mark as deprecated.
    public function expired(): bool
    {
        return $this->created_at < Carbon::now()->addMinutes(-1 * static::EXPIRE_MINUTE);
    }
}

// end
