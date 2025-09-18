<?php

namespace MetaFox\InAppPurchase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\InAppPurchase\Database\Factories\ProductFactory;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Product.
 *
 * @property        int            $id
 * @property        string         $title
 * @property        int            $item_id
 * @property        string         $item_type
 * @property        string         $ios_product_id
 * @property        string         $android_product_id
 * @property        string         $created_at
 * @property        string         $updated_at
 * @property        string         $price
 * @property        int            $is_recurring
 * @method   static ProductFactory factory(...$parameters)
 */
class Product extends Model implements Entity, HasTitle
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'iap_product';

    protected $table = 'iap_products';

    /** @var string[] */
    protected $fillable = [
        'title',
        'price',
        'item_id',
        'item_type',
        'ios_product_id',
        'android_product_id',
        'is_recurring',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_recurring' => 'bool',
    ];

    /**
     * @return ProductFactory
     */
    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    /**
     * @return string
     */
    public function toTitle(): string
    {
        return Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);
    }

    public function toType(): string
    {
        $type = InAppPur::getProductTypeByValue($this->item_type);

        if (!$type) {
            return 'Unknown';
        }

        return $type['label'];
    }

    public function toUrl(): string
    {
        $type = InAppPur::getProductTypeByValue($this->item_type);

        if (!$type) {
            return '/in-app-purchase/product/browse';
        }

        return $type['url'] ?? '/in-app-purchase/product/browse';
    }

    public function getPriceAttribute(): array
    {
        $price = Arr::get($this->attributes, 'price', '');
        if (!$price) {
            return [];
        }

        if (is_array($price)) {
            return $price;
        }

        return json_decode($price, true);
    }
}
