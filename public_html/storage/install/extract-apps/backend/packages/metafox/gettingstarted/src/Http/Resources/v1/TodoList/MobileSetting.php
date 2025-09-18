<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('showTodoList')
            ->apiUrl('getting-started/todo-list')
            ->apiRules([
                'resolution' => ['includes', 'resolution', [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE]],
            ])
            ->apiParams(['resolution' => ':resolution']);

        $this->add('showTodoListItem')
            ->apiUrl('getting-started/todo-list/:id');

        $this->add('markTodoListItem')
            ->asPost()
            ->apiUrl('getting-started/todo-list/mark');
    }
}
