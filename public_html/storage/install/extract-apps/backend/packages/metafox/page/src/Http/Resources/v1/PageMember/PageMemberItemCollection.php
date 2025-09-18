<?php

namespace MetaFox\Page\Http\Resources\v1\PageMember;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageMemberItemCollection extends ResourceCollection
{
    public $collects = PageMemberItem::class;
}
