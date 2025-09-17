<?php

namespace MetaFox\Group\Http\Resources\v1\Announcement;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

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
