<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 *--------------------------------------------------------------------------
 * ReportOwner Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAllReport')
            ->apiUrl('report-owner')
            ->asGet()
            ->apiParams(['owner_id' => ':id'])
            ->apiRules(['sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()]]);

        $this->add('keepPost')
            ->apiUrl('report-owner/:id')
            ->asPut()
            ->apiParams(['keep_post' => 1]);

        $this->add('removePost')
            ->apiUrl('report-owner/:id')
            ->asPut()
            ->apiParams(['keep_post' => 0]);

        $this->add('reportToOwner')
            ->apiUrl('report-owner')
            ->asPost()
            ->apiParams([
                'reason'    => ':reason',
                'feedback'  => ':feedback',
                'item_id'   => ':item_id',
                'item_type' => ':item_type',
            ]);

        $this->add('listUsersReportOwner')
            ->apiUrl('report-owner/reporters/:id')
            ->asGet();
    }
}
