<?php

namespace MetaFox\GettingStarted\Repositories;

use MetaFox\GettingStarted\Models\TodoList;

interface TodoListTextRepositoryInterface
{
    /**
     * @param  TodoList             $todoList
     * @param  array<string, mixed> $attributes
     * @return bool|null
     */
    public function updateOrCreateDescription(TodoList $todoList, array $attributes): ?bool;
}
