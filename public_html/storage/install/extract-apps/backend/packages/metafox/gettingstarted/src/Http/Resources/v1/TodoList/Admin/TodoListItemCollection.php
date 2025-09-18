<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class TodoListItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListItemCollection extends ResourceCollection
{
    public $collects = TodoListItem::class;
}
