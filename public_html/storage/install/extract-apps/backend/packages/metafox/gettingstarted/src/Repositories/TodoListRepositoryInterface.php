<?php

namespace MetaFox\GettingStarted\Repositories;

use MetaFox\Platform\Contracts\User;

interface TodoListRepositoryInterface
{
    public function viewTodoListForAdminCP(User $context, array $params);

    public function viewTodoList(User $context, array $params);

    public function countTodoList(array $params): int;

    public function viewTodoListDetail(User $context, int $id);

    public function createTodoListAdminCP(User $context, array $params);

    public function updateTodoListAdminCP(User $context, int $id, array $params);

    public function deleteTodoListAdminCP(User $context, int $id): void;

    public function orderTodoList(array $orderIds): bool;

    public function getRecentUndoneTodoList(User $context);
}
