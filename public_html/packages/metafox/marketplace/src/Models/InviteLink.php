<?php

namespace MetaFox\Marketplace\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class InviteLink.
 *
 * @property int $id
 */
class InviteLink extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'marketplace_invite_link';

    protected $table = 'marketplace_invite_links';

    /** @var string[] */
    protected $fillable = [
        'listing_id',
        'code',
        'status',
        'expired_at',
    ];
}

// end
