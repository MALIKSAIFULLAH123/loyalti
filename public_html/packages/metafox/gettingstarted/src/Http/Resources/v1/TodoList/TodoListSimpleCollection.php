<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TodoListSimpleCollection extends ResourceCollection
{
    public $collects = TodoListSimple::class;
}
