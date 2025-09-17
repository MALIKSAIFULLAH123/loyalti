<?php

namespace MetaFox\Announcement\Http\Resources\v1\AnnouncementView;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AnnouncementViewItemCollection extends ResourceCollection
{
    public $collects = AnnouncementViewItem::class;
}
