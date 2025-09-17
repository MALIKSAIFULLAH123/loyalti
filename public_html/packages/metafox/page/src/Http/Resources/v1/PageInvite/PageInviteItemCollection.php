<?php

namespace MetaFox\Page\Http\Resources\v1\PageInvite;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageInviteItemCollection extends ResourceCollection
{
    public $collects = PageInviteItem::class;
}
