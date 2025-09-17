<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Report\Http\Resources\v1\ReportItem\Admin;

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
        $this->add('editItem')
            ->apiUrl('admincp/report/form/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/report/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('report::phrase.delete_confirm'),
                ]
            );

        $this->add('batchDelete')
            ->apiUrl('admincp/report/batch-delete');
    }
}
