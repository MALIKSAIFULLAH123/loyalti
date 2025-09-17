<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * |--------------------------------------------------------------------------
 * | Resource Pattern
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/item_collection.stub
 */

/**
 * Class SubscriptionInvoiceItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class SubscriptionInvoiceItemCollection extends ResourceCollection
{
    public $collects = SubscriptionInvoiceItem::class;

    /**
     * @param Request $request
     * @param array   $paginated
     * @param array   $default
     *
     * @return array
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        if ($this->resource->count() > 0) {
            return $default;
        }

        $meta = Arr::get($default, 'meta');

        $default['meta'] = array_merge($meta, [
            'empty_message' => __p('core::web.no_results_found'),
        ]);

        return $default;
    }
}
