<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Contracts\Content;
/**
 * Class Ticket.
 *
 * @property int $id
 * @property int $listing_id
 * @property int $image_file_id
 * @property int $ordering
 *
 * @method ImageFactory static ImageFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @mixin Builder
 */
class Ticket extends Model implements
    Entity,
    Content,
    HasThumbnail
{
    use HasEntity;
    use HasContent;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'sevent_ticket';

    protected $table = 'sevent_tickets';

    public function userType(): string
    {
        return 'user';
    }

    public function userId(): int
    {
        return $this->id;
    }
    public function ownerType(): string
    {
        return 'user';
    }
    public function ownerEntity(): string
    {
        return 'user';
    }
    public function toTitle(): string
    {
        return $this->title;
    }

    public function ownerId(): int
    {
        return $this->id;
    }

    protected $fillable = [
        'sevent_id',
        'user_type',
        'owner_id',
        'owner_type',
        'expiry_date',
        'total_sales',
        'user_id',
        'image_file_id',
        'temp_qty',
        'description',
        'title',
        'amount',
        'is_unlimited',
        'qty'
    ];

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }
}
