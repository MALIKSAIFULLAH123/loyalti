<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class NotificationChannelItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationChannelItemCollection extends ResourceCollection
{
    public $collects = NotificationChannelItem::class;
}
