<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class UserStatsActivity
 *
 * @property int    $id
 * @property string $activity_at
 * @property int    $user_id
 * @property string $user_type
 */
class UserStatsActivity extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_stats_activity';

    protected $table = 'user_stats_activities';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'activity_at',
        'user_id',
        'user_type',
    ];

}

// end
