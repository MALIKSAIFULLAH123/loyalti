<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Featured\Http\Resources\v1\Item;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl('featured/item/form/:item_type/:item_id');

        $this->add('viewAll')
            ->apiUrl('featured/item')
            ->apiParams([
                'item_type'               => ':item_type',
                'package_id'              => ':package_id',
                'status'                  => ':status',
                'package_duration_period' => ':package_duration_period',
                'from_date'               => ':from_date',
                'pricing'                 => ':pricing',
                'to_date'                 => ':to_date',
            ])
            ->apiRules([
                'item_type'               => ['truthy', 'item_type'],
                'package_id'              => ['truthy', 'package_id'],
                'status'                  => ['truthy', 'status'],
                'package_duration_period' => ['truthy', 'package_duration_period'],
                'from_date'               => ['truthy', 'from_date'],
                'to_date'                 => ['truthy', 'to_date'],
                'pricing'                 => ['truthy', 'pricing'],
            ]);

        $this->add('searchItem')
            ->apiUrl('featured/item')
            ->placeholder(__p('featured::phrase.search_items'))
            ->apiParams([
                'item_type'               => ':item_type',
                'package_id'              => ':package_id',
                'status'                  => ':status',
                'package_duration_period' => ':package_duration_period',
                'from_date'               => ':from_date',
                'to_date'                 => ':to_date',
                'pricing'                 => ':pricing',
            ])
            ->apiRules([
                'item_type'               => ['truthy', 'item_type'],
                'package_id'              => ['truthy', 'package_id'],
                'status'                  => ['truthy', 'status'],
                'package_duration_period' => ['truthy', 'package_duration_period'],
                'from_date'               => ['truthy', 'from_date'],
                'to_date'                 => ['truthy', 'to_date'],
                'pricing'                 => ['truthy', 'pricing'],
            ]);

        $this->add('searchForm')
            ->apiUrl('core/mobile/form/featured.item.search_form');

        $this->add('cancelItem')
            ->asPatch()
            ->apiUrl('featured/item/:id/cancel')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('featured::phrase.are_you_sure_you_want_to_cancel_this_featured_item'),
            ]);

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('featured/item/:id')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('featured::phrase.delete_featured_item_description'),
            ]);

        $this->add('viewItem')
            ->apiUrl('featured/item/:id');
    }
}
