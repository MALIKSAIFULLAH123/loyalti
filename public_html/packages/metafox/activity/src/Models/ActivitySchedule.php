<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ActivitySchedule.
 *
 * @property int    $id
 * @property string $type_id
 * @property array  $data
 * @property string $schedule_time
 * @property string $post_type
 * @property string $content
 * @property int    $is_temp
 */
class ActivitySchedule extends Model implements Entity, Content
{
    use HasContent;
    use HasEntity;
    use HasAmountsTrait;
    use HasOwnerMorph;
    use HasUserMorph;

    public const ENTITY_TYPE = 'activity_schedule';

    protected $table = 'activity_schedules';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'data',
        'content',
        'post_type',
        'schedule_time',
        'is_temp',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data'          => 'array',
        'schedule_time' => 'datetime',
    ];

    public function getPrivacyAttribute(): int
    {
        return Arr::get($this->data, 'privacy');
    }

    public function getItemAttribute(): self
    {
        return $this;
    }

    public function toTitle(): string
    {
        return __p('activity::phrase.scheduled_posts');
    }
}
