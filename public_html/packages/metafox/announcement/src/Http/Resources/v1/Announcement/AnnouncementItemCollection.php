<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AnnouncementItemCollection extends ResourceCollection
{
    public $collects = AnnouncementItem::class;
}
