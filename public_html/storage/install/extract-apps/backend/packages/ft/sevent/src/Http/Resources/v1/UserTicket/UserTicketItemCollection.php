<?php

namespace Foxexpert\Sevent\Http\Resources\v1\UserTicket;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserTicketItemCollection extends ResourceCollection
{
    protected $collect = UserTicketItem::class;
}
