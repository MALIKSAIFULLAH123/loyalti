<?php

namespace MetaFox\Announcement\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AnnouncementData.
 *
 * @property int $id
 */
class AnnouncementData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    public const ENTITY_TYPE = 'announcement_role_data';

    protected $table = 'announcement_role_data';

    /** @var string[] */
    protected $fillable = [
        'id',
        'announcement_id',
        'role_id',
    ];
}

// end
