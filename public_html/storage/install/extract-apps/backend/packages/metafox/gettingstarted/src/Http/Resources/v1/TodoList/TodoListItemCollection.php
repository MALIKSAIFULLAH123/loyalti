<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TodoListItemCollection extends ResourceCollection
{
    public $collects = TodoListItem::class;
}
