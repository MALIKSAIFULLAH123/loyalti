<?php

namespace MetaFox\Marketplace\Support\Browse\Scopes\Listing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class FilterPriceScope extends BaseScope
{
    public function __construct(protected ?float $priceFrom = null, protected ?float $priceTo = null)
    {
    }

    /**
     * @return float|null
     */
    public function getPriceFrom(): ?float
    {
        return $this->priceFrom;
    }

    /**
     * @return float|null
     */
    public function getPriceTo(): ?float
    {
        return $this->priceTo;
    }

    /**
     * @param  Builder $builder
     * @param  Model   $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (null === $this->priceFrom && null === $this->priceTo) {
            return;
        }

        $builder->whereNotNull('marketplace_listing_prices.price');

        if (null !== $this->priceFrom) {
            $builder->where('marketplace_listing_prices.price', '>=', $this->priceFrom);
        }

        if (null !== $this->priceTo) {
            $builder->where('marketplace_listing_prices.price', '<=', $this->priceTo);
        }
    }
}
