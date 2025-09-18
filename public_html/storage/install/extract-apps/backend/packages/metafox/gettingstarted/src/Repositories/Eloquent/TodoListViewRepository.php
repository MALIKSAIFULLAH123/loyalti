<?php

namespace MetaFox\GettingStarted\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\GettingStarted\Policies\TodoListViewPolicy;
use MetaFox\GettingStarted\Repositories\TodoListViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\GettingStarted\Models\TodoListView as Model;

/**
 * Class TodoListViewRepository.
 * @property Model $model
 * @method   Model getModel()
 * @method   Model find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListViewRepository extends AbstractRepository implements TodoListViewRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function markDone(User $user, array $attributes): void
    {
        $todoListId  = Arr::get($attributes, 'todo_list_id');
        $todoListIds = Arr::get($attributes, 'todo_list_ids');

        policy_authorize(TodoListViewPolicy::class, 'markDone', $user);

        $markDone = function ($id) use ($user) {
            $viewExist = $this->getModel()->newQuery()
                ->where('todo_list_id', $id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$viewExist) {
                $this->create(['todo_list_id' => $id, 'user_id' => $user->id]);
            }
        };

        if ($todoListId) {
            $markDone($todoListId);
        }

        if ($todoListIds) {
            foreach ($todoListIds as $id) {
                $markDone($id);
            }
        }
    }

    public function isDone(int $todoListId, int $userId): bool
    {
        return $this->getModel()->newQuery()
            ->where('todo_list_id', $todoListId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function checkViewExist(array $conditions): bool
    {
        return $this->getModel()->newQuery()
            ->where($conditions)
            ->exists();
    }
}
