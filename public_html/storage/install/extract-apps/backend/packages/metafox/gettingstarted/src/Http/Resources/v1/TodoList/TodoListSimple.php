<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\GettingStarted\Support\Traits\TodoListTrait;

class TodoListSimple extends JsonResource
{
    use TodoListTrait;

    public function toArray($request): array
    {
        return [
            'title'    => $this->resource->title,
            'is_done'  => $this->isDone($this->resource->entityId(), user()->id),
            'ordering' => $this->resource->ordering,
        ];
    }
}
