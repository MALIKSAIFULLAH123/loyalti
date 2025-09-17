<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * ReportReason Web Resource Setting
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
        $this->add('addItem')
            ->apiUrl('admincp/report/reason/form');

        $this->add('editItem')
            ->apiUrl('admincp/report/reason/form/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/report/reason/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('report::phrase.delete_confirm_report_reason'),
                ]
            );
    }
}
