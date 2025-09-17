<?php

namespace MetaFox\Announcement\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Announcement\Database\Factories\AnnouncementCloseFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AnnouncementClose.
 *
 * @property int $id
 */
class AnnouncementClose extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'announcement_close';

    protected $table = 'announcement_closes';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'announcement_id',
    ];
}

// end
