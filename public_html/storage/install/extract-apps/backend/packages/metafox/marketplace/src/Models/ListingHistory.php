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
 * Class ListingHistory.
 *
 * @property int         $id
 * @property int         $listing_id
 * @property int         $user_id
 * @property string      $user_type
 * @property string|null $visited_at
 */
class ListingHistory extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'marketplace_listing_history';

    protected $table = 'marketplace_listing_histories';

    /** @var string[] */
    protected $fillable = [
        'listing_id',
        'user_id',
        'user_type',
        'visited_at',
    ];

    public $timestamps = false;
}
