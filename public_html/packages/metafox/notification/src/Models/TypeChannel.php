<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Notification\Database\Factories\TypeChannelFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class TypeChannel.
 *
 * @property int    $id
 * @property Type   $type
 * @method   static TypeChannelFactory factory(...$parameters)
 */
class TypeChannel extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'notification_type_channel';

    protected $table = 'notification_type_channels';

    /** @var string[] */
    protected $fillable = [
        'type_id',
        'channel',
        'is_active',
        'ordering',
    ];

    /**
     * @return TypeChannelFactory
     */
    protected static function newFactory()
    {
        return TypeChannelFactory::new();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}

// end
