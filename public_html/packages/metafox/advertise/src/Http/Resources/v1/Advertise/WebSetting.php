<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
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
            ->pageUrl('advertise');

        $this->add('addItem')
            ->apiUrl('core/form/advertise.store')
            ->pageUrl('advertise/add');

        $this->add('editItem')
            ->apiUrl('core/form/advertise.edit/:id')
            ->pageUrl('advertise/edit/:id');

        $this->add('deleteItem')
            ->apiUrl('advertise/advertise/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_advertise_permanently'),
                ]
            );

        $this->add('activeItem')
            ->apiUrl('advertise/advertise/active/:id')
            ->apiParams([
                'active' => ':active',
            ])
            ->asPatch();

        $this->add('viewItem')
            ->pageUrl('advertise/:id')
            ->apiUrl('advertise/advertise/:id')
            ->apiRules([
                'view'                                        => ['truthy', 'view'],
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_WEEK],
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_WEEK],
                'start_date_' . Support::STATISTIC_VIEW_MONTH => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_MONTH],
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_MONTH],
                'start_date_' . Support::STATISTIC_VIEW_DAY   => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_DAY],
                'end_date_' . Support::STATISTIC_VIEW_DAY     => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_DAY],
            ]);

        $this->add('viewAll')
            ->apiUrl('advertise/advertise')
            ->apiParams([
                'placement_id' => ':placement_id',
                'view'         => ':view',
                'start_date'   => ':start_date',
                'end_date'     => ':end_date',
                'status'       => ':status',
            ])
            ->apiRules([
                'placement_id' => ['truthy', 'placement_id'],
                'view'         => ['includes', 'view', Facade::getAllowedViews()],
                'start_date'   => ['truthy', 'start_date'],
                'end_date'     => ['truthy', 'end_date'],
                'status'       => ['includes', 'status', Facade::getAdvertiseStatuses()],
            ]);

        $this->add('paymentItem')
            ->apiUrl('core/form/advertise.payment/:id');

        $this->add('searchForm')
            ->apiUrl('core/form/advertise.search_form');

        $this->add('showAll')
            ->apiUrl('advertise/advertise/show')
            ->apiParams([
                'placement_id' => ':placement_id',
                'location'     => ':location',
            ])
            ->apiRules([
                'placement_id' => ['truthy', 'placement_id'],
                'location'     => ['includes', 'location', Facade::getAllowedLocations()],
            ]);

        $this->add('updateTotalItem')
            ->apiUrl('advertise/advertise/total/:id')
            ->apiParams([
                'type' => ':type',
            ])
            ->apiRules([
                'type' => ['includes', 'type', [Support::TYPE_CLICK, Support::TYPE_IMPRESSION]],
            ])
            ->asPatch();

        $this->add('viewClickReport')
            ->apiUrl('advertise/advertise/report/:id')
            ->asGet()
            ->apiParams([
                'report_type'                                 => Support::TYPE_CLICK,
                'view'                                        => ':view',
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => ':start_date_' . Support::STATISTIC_VIEW_WEEK,
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => ':end_date_' . Support::STATISTIC_VIEW_WEEK,
                'start_date_' . Support::STATISTIC_VIEW_MONTH => ':start_date_' . Support::STATISTIC_VIEW_MONTH,
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => ':end_date_' . Support::STATISTIC_VIEW_MONTH,
                'start_date_' . Support::STATISTIC_VIEW_DAY   => ':start_date_' . Support::STATISTIC_VIEW_DAY,
                'end_date_' . Support::STATISTIC_VIEW_DAY     => ':end_date_' . Support::STATISTIC_VIEW_DAY,
            ])
            ->apiRules([
                'view'                                        => ['truthy', 'view'],
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_WEEK],
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_WEEK],
                'start_date_' . Support::STATISTIC_VIEW_MONTH => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_MONTH],
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_MONTH],
                'start_date_' . Support::STATISTIC_VIEW_DAY   => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_DAY],
                'end_date_' . Support::STATISTIC_VIEW_DAY     => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_DAY],
            ]);

        $this->add('viewImpressionReport')
            ->apiUrl('advertise/advertise/report/:id')
            ->asGet()
            ->apiParams([
                'report_type'                                 => Support::TYPE_IMPRESSION,
                'view'                                        => ':view',
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => ':start_date_' . Support::STATISTIC_VIEW_WEEK,
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => ':end_date_' . Support::STATISTIC_VIEW_WEEK,
                'start_date_' . Support::STATISTIC_VIEW_MONTH => ':start_date_' . Support::STATISTIC_VIEW_MONTH,
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => ':end_date_' . Support::STATISTIC_VIEW_MONTH,
                'start_date_' . Support::STATISTIC_VIEW_DAY   => ':start_date_' . Support::STATISTIC_VIEW_DAY,
                'end_date_' . Support::STATISTIC_VIEW_DAY     => ':end_date_' . Support::STATISTIC_VIEW_DAY,
            ])
            ->apiRules([
                'view'                                        => ['truthy', 'view'],
                'start_date_' . Support::STATISTIC_VIEW_WEEK  => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_WEEK],
                'end_date_' . Support::STATISTIC_VIEW_WEEK    => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_WEEK],
                'start_date_' . Support::STATISTIC_VIEW_MONTH => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_MONTH],
                'end_date_' . Support::STATISTIC_VIEW_MONTH   => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_MONTH],
                'start_date_' . Support::STATISTIC_VIEW_DAY   => ['truthy', 'start_date_' . Support::STATISTIC_VIEW_DAY],
                'end_date_' . Support::STATISTIC_VIEW_DAY     => ['truthy', 'end_date_' . Support::STATISTIC_VIEW_DAY],
            ]);

        $this->add('hideItem')
            ->apiUrl('advertise/advertise/hide/:id')
            ->asPatch();

        $this->add('getChartForm')
            ->apiUrl('core/form/advertise.search_chart_form/:id')
            ->asGet();

        $this->add('getGrid')
            ->apiUrl('core/grid/advertise');
    }
}
