<?php

namespace MetaFox\GettingStarted\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\GettingStarted\Models\TodoList;
use MetaFox\GettingStarted\Repositories\TodoListTextRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\GettingStarted\Models\TodoListText as Model;

class TodoListTextRepository extends AbstractRepository implements TodoListTextRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function updateOrCreateDescription(TodoList $todoList, array $attributes): ?bool
    {
        $descriptions = Arr::get($attributes, 'text') ?: [];

        if (!is_array($descriptions)) {
            return false;
        }

        if (empty($descriptions)) {
            return true;
        }

        foreach ($descriptions as $locale => $content) {
            $cleanedContent = parse_input()->clean($content, false, true); // Ensure this removes tags
            $parsedContent  = parse_input()->prepare($content); // Ensure this keeps tags

            $this->getModel()->newQuery()->updateOrCreate(
                [
                    'todo_list_id' => $todoList->entityId(),
                    'locale'       => $locale,
                ],
                [
                    'text'        => $cleanedContent,
                    'text_parsed' => $parsedContent,
                ]
            );
        }

        return true;
    }
}
