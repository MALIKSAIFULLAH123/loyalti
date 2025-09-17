<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Report\Http\Resources\v1\ReportItem;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * ReportItem Web Resource Setting
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
        $this->add('reportItem')
            ->apiUrl('report')
            ->asPost()
            ->apiParams([
                'reason'    => ':reason',
                'feedback'  => ':feedback',
                'item_id'   => ':item_id',
                'item_type' => ':item_type',
            ]);
    }
}
