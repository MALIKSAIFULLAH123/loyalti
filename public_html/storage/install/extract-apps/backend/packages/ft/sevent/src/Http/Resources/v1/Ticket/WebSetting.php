<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1\Ticket;

use Foxexpert\Sevent\Support\Browse\Scopes\Ticket\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Ticket Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class TicketWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('deleteItem')
            ->apiUrl('sevent/ticket/:id')
            ->pageUrl('ticket')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('sevent::web.delete_ticket_confirm'),
                ]
            );
     $this->add('viewAll')
            ->pageUrl('sevent/ticket/all')
            ->apiUrl('sevent/ticket')
            ->apiRules([
                'sevent_id' => [ 'truthy', 'sevent_id']
            ]);
        $this->add('editItem')
            ->pageUrl('sevent/ticket/edit/:id')
            ->apiUrl('core/form/sevent_ticket.update/:id');

        $this->add('paymentItem')
            ->apiUrl('core/form/sevent.payment/:id')
            ->asGet();

        $this->add('addItem')
             ->pageUrl('sevent/ticket/add/:id')
            ->apiUrl('core/form/sevent_ticket.store');
    }
}