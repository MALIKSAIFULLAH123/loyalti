<?php

namespace MetaFox\GettingStarted\Support\Traits;

use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;

trait TodoListTrait
{
    public function isDone(int $todoListId, int $userId): bool
    {
        return resolve(TodoListViewRepositoryInterface::class)->isDone($todoListId, $userId);
    }

    public function getTotalTodoList(array $params): int
    {
        return resolve(TodoListRepositoryInterface::class)->countTodoList($params);
    }

    public function getRecentUndoneTodoListOrdering(User $context): int
    {
        $todoList = resolve(TodoListRepositoryInterface::class)->getRecentUndoneTodoList($context);

        return !empty($todoList) ? (int) $todoList->ordering : 1;
    }

    public function checkViewExist(array $conditions): bool
    {
        return resolve(TodoListViewRepositoryInterface::class)->checkViewExist($conditions);
    }
}
