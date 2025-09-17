<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use Illuminate\Support\Arr;
use MetaFox\GettingStarted\Models\TodoList as Model;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class UpdateCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateTodoListForm extends StoreTodoListForm
{
    protected TodoListRepositoryInterface $repository;

    public function boot(TodoListRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $this->repository->find($id);
    }

    protected function prepare(): void
    {
        $model = $this->resource;

        $descriptions = collect($this->resource->descriptions)->pluck('text_parsed', 'locale')->toArray();

        $values = [
            'title'      => Language::getPhraseValues($model->title_var),
            'text'       => $descriptions,
            'resolution' => $model->resolution,
        ];
        $values = $this->prepareAttachedPhotos($values);

        $this->asPut()->title(__p('getting-started::phrase.edit_todo_list'))
            ->action(url_utility()->makeApiUrl('admincp/getting-started/todo-list/' . $this->resource->id))
            ->setValue($values);
    }

    protected function prepareAttachedPhotos(array $values): array
    {
        $items = [];

        if ($this->resource->images->count()) {
            $items = $this->resource->images->map(function ($photo) {
                return ResourceGate::asItem($photo, null);
            });
        }

        Arr::set($values, 'attached_photos', $items);

        return $values;
    }
}
