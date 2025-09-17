<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class AnnouncementItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementItemCollection extends ResourceCollection
{
    public $collects = AnnouncementItem::class;
}
