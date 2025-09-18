<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/marketplace.invoice.search_form');

        $this->add('viewDetail')
            ->apiUrl('admincp/marketplace/invoice/:id/short-transaction');
    }
}
