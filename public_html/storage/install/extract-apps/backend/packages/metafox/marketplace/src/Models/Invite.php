<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Marketplace\Database\Factories\InviteFactory;
use MetaFox\Marketplace\Notifications\InviteNotification;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Invite.
 *
 * @property int    $id
 * @property int    $listing_id
 * @property int    $type_id
 * @property int    $visited_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property string $owner_type
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static InviteFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @mixin Builder
 */
class Invite extends Model implements IsNotifyInterface
{
    use HasEntity;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'marketplace_invite';

    protected $table = 'marketplace_invites';

    protected $fillable = [
        'listing_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'method_type',
        'method_value',
        'visited_at',
    ];

    protected static function newFactory(): InviteFactory
    {
        return InviteFactory::new();
    }

    public function toNotification(): array
    {
        return [$this->owner, new InviteNotification($this)];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'id');
    }
}
