<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class NotificationModuleItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationModuleItemCollection extends ResourceCollection
{
    public $collects = NotificationModuleItem::class;
}
