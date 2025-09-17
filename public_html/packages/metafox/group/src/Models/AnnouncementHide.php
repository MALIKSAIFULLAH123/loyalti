<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AnnouncementHide.
 *
 * @property int    $id
 * @property int    $announcement_id
 * @property int    $user_id
 * @property string $user_type
 */
class AnnouncementHide extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'group_announcement_hidden';

    protected $table = 'group_announcement_hidden';

    /** @var string[] */
    protected $fillable = [
        'announcement_id',
        'group_id',
        'user_id',
        'user_type',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(\MetaFox\Announcement\Models\Announcement::class, 'announcement_id', 'id');
    }
}

// end
