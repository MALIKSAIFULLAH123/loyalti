<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1\Invoice;

use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('sevent-invoice')
            ->apiParams([
                'view'       => ':view',
                'sevent_id' => ':sevent_id',
                'from'       => ':from',
                'to'         => ':to',
                'status'     => ':status',
            ])
            ->apiRules([
                'view'       => ['includes', 'view', ViewScope::getAllowView()],
                'sevent_id' => ['truthy', 'sevent_id'],
                'from'       => ['truthy', 'from'],
                'to'         => ['truthy', 'to'],
                'status'     => ['truthy', 'status'],
            ])
            ->asGet();

        $this->add('viewItem')
            ->apiUrl('sevent-invoice/:id')
            ->pageUrl('sevent/invoice/:id');

        $this->add('getBoughtSearchForm')
            ->apiParams([
                'sevent_id' => ':sevent_id',
            ])
            ->apiUrl('core/form/sevent_invoice.bought_search');

        $this->add('getSoldSearchForm')
            ->apiParams([
                'sevent_id' => ':sevent_id',
            ])
            ->apiUrl('core/form/sevent_invoice.sold_search');
    }
}
