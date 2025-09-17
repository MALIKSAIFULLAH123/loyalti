<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class InactiveProcessData
 *
 * @property int $id
 * @property int $process_id
 * @property int $status
 */
class InactiveProcessData extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_inactive_process_data';

    protected $table = 'user_inactive_process_data';

    /** @var string[] */
    protected $fillable = [
        'process_id',
        'user_id',
        'user_type',
        'status',
    ];

    public function isStopped(): bool
    {
        return $this->status === InactiveProcess::STOPPED_STATUS;
    }
}

// end
