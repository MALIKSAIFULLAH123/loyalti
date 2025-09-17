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
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('advertise/sponsor');

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

        $this->add('addItem')
            ->pageUrl('advertise/sponsor/add?item_type=:resource_name&item_id=:id')
            ->apiUrl('advertise/sponsor/form/:resource_name/:id')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_MOBILE,
            ]);

        $this->add('addFeedItem')
            ->pageUrl('advertise/sponsor/feed/add?item_type=:resource_name&item_id=:id')
            ->apiUrl('advertise/sponsor/form/feed/:resource_name/:id')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_MOBILE,
            ]);

        $this->add('activeItem')
            ->apiUrl('advertise/sponsor/active/:id')
            ->apiParams([
                'is_active' => ':is_active',
            ])
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('advertise/sponsor/:id')
            ->asDelete()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_sponsor_permanently'),
            ]);

        $this->add('editItem')
            ->apiUrl('core/mobile/form/advertise.advertise_sponsor.update/:id')
            ->pageUrl('advertise/sponsor/edit/:id');

        $this->add('searchForm')
            ->apiUrl('core/mobile/form/advertise_sponsor.search_form');

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

        $this->add('searchItem')
            ->apiUrl('advertise/invoice')
            ->placeholder(__p('advertise::phrase.search_sponsor'))
            ->apiParams([
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
                'status'     => ':status',
            ]);
    }
}
