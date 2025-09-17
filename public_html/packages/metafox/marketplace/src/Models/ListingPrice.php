<?php

namespace MetaFox\Marketplace\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Marketplace\Database\Factories\ListingPriceFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ListingPrice.
 *
 * @property        int                 $id
 * @method   static ListingPriceFactory factory(...$parameters)
 */
class ListingPrice extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'marketplace_listing_price';

    protected $table = 'marketplace_listing_prices';

    /** @var string[] */
    protected $fillable = [
        'listing_id',
        'currency_id',
        'price',
    ];

    public $timestamps = false;

    /**
     * @return ListingPriceFactory
     */
    protected static function newFactory()
    {
        return ListingPriceFactory::new();
    }
}

// end
