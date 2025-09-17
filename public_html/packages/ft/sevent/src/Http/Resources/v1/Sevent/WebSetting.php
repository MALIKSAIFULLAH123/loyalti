<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\MetaFoxConstant;
/**
 *--------------------------------------------------------------------------
 * Sevent Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class SeventWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $apiRules = [
            'q' => [
                'truthy', 'q',
            ], 
            'country_iso' => [
                'truthy', 'country_iso',
            ], 
            'sort'         => ['truthy', 'sort'],
            'distance'         => ['truthy', 'distance'],
            'when'         => ['truthy', 'when'],
            'view'         => [
                'includes',
                'view',
                ViewScope::getAllowView(),
            ],
            'sview'         => [
                'includes',
                'sview',
                ViewScope::getAllowView(),
            ],
            'tag'         => ['truthy', 'tag'], 'category_id' => ['truthy', 'category_id'],
            'is_featured' => ['truthy', 'is_featured'],
            'bounds_west'  => ['truthy', 'bounds_west'],
            'bounds_east'  => ['truthy', 'bounds_east'],
            'bounds_south' => ['truthy', 'bounds_south'],
            'bounds_north' => ['truthy', 'bounds_north'],
            'zoom'         => ['truthy', 'zoom'],
        ];
        $this->add('searchItem')
            ->pageUrl('sevent/search')
            ->pageParams(['view' => Browse::VIEW_SEARCH])
            ->placeholder(__p('sevent::phrase.search_sevents'));

        $this->add('homePage')
            ->pageUrl('sevent');

        $this->add('viewEventsMap')
            ->apiUrl('sevent')
            ->apiRules($apiRules);

        $this->add('viewAll')
            ->pageUrl('sevent/all')
            ->apiUrl('sevent')
            ->apiRules($apiRules);

        $this->add('viewItem')
            ->pageUrl('sevent/:id')
            ->apiUrl('sevent/:id');

        $this->add('paymentItem')
            ->apiUrl('core/form/sevent.payment/:id')
            ->asGet();

        $this->add('deleteItem')
            ->apiUrl('sevent/:id')
            ->pageUrl('sevent')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('sevent::phrase.delete_confirm'),
                ]
            );

        $this->add('editItem')
            ->pageUrl('sevent/edit/:id')
            ->apiUrl('core/form/sevent.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('sevent/edit/:id')
            ->apiUrl('core/form/sevent.update/:id');

        $this->add('addItem')
            ->pageUrl('sevent/add')
            ->apiUrl('core/form/sevent.store');

        $this->add('publishSevent')
            ->apiUrl('sevent/publish/:id')
            ->asPatch()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('sevent::phrase.publish_sevent_confirm'),
            ]);

        $this->add('approveItem')
            ->apiUrl('sevent/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('sevent/sponsor/:id');

        $this->add('addTicketItem')
            ->apiUrl('core/form/sevent_ticket.store')
            ->apiParams([
                'sevent_id' => ':id',
            ]);    
        $this->add('favourite')
            ->apiUrl('sevent/favourite/:id')
            ->asPatch();
        $this->add('favouriteCheck')
            ->apiUrl('sevent/favouriteCheck/:id')
            ->asPatch();
        $this->add('sponsorItemInFeed')
            ->apiUrl('sevent/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('featureItem')
            ->apiUrl('sevent/feature/:id');

        $this->add('getBackers')
            ->apiUrl('sevent/getBackers/');
            
        $this->add('getCategories')
        ->apiUrl('sevent/getCategories/');

        $this->add('getTopBloggers')
            ->apiUrl('sevent/getTopBloggers/');
    }
}