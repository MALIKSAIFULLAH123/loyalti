<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Notification\Database\Factories\NotificationModuleFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class NotificationModule.
 *
 * @property        int      $id
 * @property        string   $title
 * @property        string   $module_id
 * @property        bool     $is_active
 * @property        string[] $channel
 * @property        int      $ordering
 * @method   static NotificationModuleFactory factory(...$parameters)
 */
class NotificationModule extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'notification_module';

    protected $table = 'notification_modules';

    /** @var string[] */
    protected $fillable = [
        'title',
        'module_id',
        'is_active',
        'channel',
        'ordering',
    ];

    /**
     * @return NotificationModuleFactory
     */
    protected static function newFactory()
    {
        return NotificationModuleFactory::new();
    }

    public function types(): HasMany
    {
        $relation = $this->hasMany(Type::class, 'module_id', 'module_id');

        return $relation;
    }

    public function moduleChannels(): HasMany
    {
        return $this->hasMany($this, 'module_id', 'module_id');
    }
}

// end
