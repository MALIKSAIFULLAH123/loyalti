<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('advertise/sponsor');

        $this->add('updateTotalView')
            ->apiUrl('sponsor/total/view')
            ->asPost()
            ->apiParams([
                'item_type' => ':item_type',
                'item_ids'  => ':item_ids',
            ]);

        $this->add('updateTotalClick')
            ->apiUrl('sponsor/total/click')
            ->asPost()
            ->apiParams([
                'item_type' => ':item_type',
                'item_ids'  => ':item_ids',
            ]);

        $this->add('paymentItem')
            ->apiUrl('core/form/advertise.advertise_sponsor.payment/:id');

        $this->add('addItem')
            ->pageUrl('advertise/sponsor/add?item_type=:resource_name&item_id=:id')
            ->apiUrl('advertise/sponsor/form/:item_type/:item_id')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_WEB,
            ]);

        $this->add('addFeedItem')
            ->pageUrl('advertise/sponsor/feed/add?item_type=:resource_name&item_id=:id')
            ->apiUrl('advertise/sponsor/form/feed/:item_type/:item_id')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_WEB,
            ]);

        $this->add('editItem')
            ->apiUrl('core/form/advertise.advertise_sponsor.update/:id')
            ->pageUrl('advertise/sponsor/edit/:id');

        $this->add('searchForm')
            ->apiUrl('core/form/advertise.advertise_sponsor.search_form');

        $this->add('viewAll')
            ->apiUrl('advertise/sponsor')
            ->apiParams([
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
                'status'     => ':status',
            ])
            ->apiRules([
                'start_date' => ['truthy', 'start_date'],
                'end_date'   => ['truthy', 'end_date'],
                'status'     => ['includes', 'status', Facade::getAdvertiseStatuses()],
            ]);

        $this->add('activeItem')
            ->apiUrl('advertise/sponsor/active/:id')
            ->apiParams([
                'active' => ':active',
            ])
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('advertise/sponsor/:id')
            ->asDelete()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_sponsor_permanently'),
            ]);

        $this->add('getGrid')
            ->apiUrl('core/grid/advertise.sponsor');
    }
}
