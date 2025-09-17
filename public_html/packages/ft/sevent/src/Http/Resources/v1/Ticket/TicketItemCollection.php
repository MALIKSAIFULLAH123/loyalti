<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Ticket;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TicketItemCollection extends ResourceCollection
{
    protected $collect = TicketItem::class;
}
