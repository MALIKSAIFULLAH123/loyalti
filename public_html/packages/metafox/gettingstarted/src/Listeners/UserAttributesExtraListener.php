<?php

namespace MetaFox\GettingStarted\Listeners;

use MetaFox\GettingStarted\Support\Traits\GettingStartedTrait;
use MetaFox\GettingStarted\Support\Traits\TodoListTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;

class UserAttributesExtraListener
{
    use GettingStartedTrait;
    use TodoListTrait;

    public function handle(User $context, ?Entity $resource): array
    {
        $resolution = MetaFox::getResolution();

        $welcomeMessage = __p('getting-started::phrase.getting_started_welcome_message', ['name' => $context->toTitle()]);

        $totalTodoList = $this->getTotalTodoList(['resolution' => $resolution]);

        return [
            'getting_started' => [
                'welcome_message' => $welcomeMessage, //For mobile
                'is_first_login'  => $totalTodoList > 0 && $this->isFirstLogin($context),
                'total_todo_list' => $totalTodoList,
                'ordering'        => $this->getRecentUndoneTodoListOrdering($context),
            ],
        ];
    }
}
