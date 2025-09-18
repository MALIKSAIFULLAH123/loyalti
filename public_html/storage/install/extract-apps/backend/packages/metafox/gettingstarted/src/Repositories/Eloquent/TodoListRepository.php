<?php

namespace MetaFox\GettingStarted\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\GettingStarted\Models\TodoList as Model;
use MetaFox\GettingStarted\Policies\TodoListPolicy;
use MetaFox\GettingStarted\Repositories\TodoListImageRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListTextRepositoryInterface;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Class TodoListRepository.
 * @property Model $model
 * @method   Model getModel()
 * @method   Model find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListRepository extends AbstractRepository implements TodoListRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function viewTodoList(User $context, array $params)
    {
        $limit = Arr::get($params, 'limit');

        $query = $this->buildQuery($params);

        return $query
            ->with(['descriptions', 'images'])
            ->paginate($limit);
    }

    public function countTodoList(array $params = []): int
    {
        return $this->buildQuery($params)->count();
    }

    public function viewTodoListDetail(User $context, int $id)
    {
        $resource = $this->with(['descriptions', 'images'])->find($id);

        return $resource;
    }

    public function viewTodoListForAdminCP(User $context, array $params)
    {
        policy_authorize(TodoListPolicy::class, 'viewAdminCP', $context);

        $limit      = $params['limit'];
        $resolution = Arr::get($params, 'resolution');
        $search     = $params['q'] ?? '';

        $query  = $this->getModel()->newModelQuery()->select(['gettingstarted_todo_lists.*']);

        if ($search != '') {
            $table    = $this->model->getTable();

            $defaultLocale = Language::getDefaultLocaleId();

            $query->leftJoin('phrases as ps', function (JoinClause $join) use ($table) {
                $join->on('ps.key', '=', "$table.title");
            });

            $query->where(function (Builder $builder) use ($table, $search, $defaultLocale) {
                $builder->where(DB::raw("CASE when ps.name is null then $table.title else ps.text end"), $this->likeOperator(), '%' . $search . '%');
                $builder->whereRaw("CASE when ps.name is null then ps.locale is null else ps.locale = '$defaultLocale' end");
            });
        }

        if ($resolution && $resolution !== Helper::ALL) {
            $query = $query->where('resolution', $resolution);
        }

        return $query
            ->with(['descriptions', 'images'])
            ->limit($limit)
            ->orderBy('gettingstarted_todo_lists.resolution', 'desc')
            ->orderBy('gettingstarted_todo_lists.ordering')
            ->paginate();
    }

    public function createTodoListAdminCP(User $context, array $params)
    {
        policy_authorize(TodoListPolicy::class, 'createAdminCP', $context);

        $lastOrdering = $this->getModel()
            ->newQuery()
            ->where('resolution', $params['resolution'])
            ->max('ordering');

        $params['ordering'] = $lastOrdering + 1;

        $todoList = $this->getModel()
            ->newQuery()
            ->create($params);

        resolve(TodoListTextRepositoryInterface::class)->updateOrCreateDescription($todoList, $params);

        $this->handleAttachedPhotos($context, $todoList, Arr::get($params, 'attached_photos'), false);

        $todoList->refresh();

        return $todoList;
    }

    public function updateTodoListAdminCP(User $context, int $id, array $params)
    {
        policy_authorize(TodoListPolicy::class, 'updateAdminCP', $context);

        $todoList = $this->getModel()
            ->newQuery()
            ->findOrFail($id);

        $params['resolution'] = $todoList?->resolution;

        $todoList->fill($params);
        $todoList->save();

        resolve(TodoListTextRepositoryInterface::class)->updateOrCreateDescription($todoList, $params);

        $this->handleAttachedPhotos($context, $todoList, Arr::get($params, 'attached_photos'), true);

        $todoList->refresh();

        return $todoList;
    }

    public function deleteTodoListAdminCP(User $context, int $id): void
    {
        policy_authorize(TodoListPolicy::class, 'deleteAdminCP', $context);

        $todoList = $this->getModel()
            ->newQuery()
            ->where('id', $id)
            ->first();

        if ($todoList) {
            $resolution = $todoList->resolution;

            $todoList->delete();

            $todoLists = $this->getModel()
                ->newQuery()
                ->where('resolution', $resolution)
                ->orderBy('ordering')
                ->get();

            $ordering = 1;
            foreach ($todoLists as $todoList) {
                $todoList->update(['ordering' => $ordering++]);
            }
        }
    }

    public function orderTodoList(array $orderIds): bool
    {
        $todoLists = $this->getModel()->newQuery()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$todoLists->count()) {
            return true;
        }

        $webTodoLists = $todoLists->where('resolution', 'web');

        $mobileTodoLists = $todoLists->where('resolution', 'mobile');

        if ($webTodoLists->count()) {
            $allWebOrderIds      = $webTodoLists->pluck('id')->toArray();
            $filteredWebOrderIds = array_filter($orderIds, function ($id) use ($allWebOrderIds) {
                return in_array($id, $allWebOrderIds);
            });
            $ordering = 1;

            foreach ($filteredWebOrderIds as $filteredWebOrderId) {
                $webTodoLists[$filteredWebOrderId]->update(['ordering' => $ordering++]);
            }
        }

        if ($mobileTodoLists->count()) {
            $allMobileOrderIds      = $mobileTodoLists->pluck('id')->toArray();
            $filteredMobileOrderIds = array_filter($orderIds, function ($id) use ($allMobileOrderIds) {
                return in_array($id, $allMobileOrderIds);
            });
            $ordering = 1;

            foreach ($filteredMobileOrderIds as $filteredMobileOrderId) {
                $mobileTodoLists[$filteredMobileOrderId]->update(['ordering' => $ordering++]);
            }
        }

        return true;
    }

    public function getRecentUndoneTodoList(User $context): ?Model
    {
        $resolution = MetaFox::getResolution();

        $query = $this->getModel()
            ->newModelQuery()
            ->select(['gettingstarted_todo_lists.*'])
            ->from('gettingstarted_todo_lists')
            ->leftJoin('gettingstarted_todo_list_views', function (JoinClause $join) use ($context) {
                $join->on('gettingstarted_todo_lists.id', '=', 'gettingstarted_todo_list_views.todo_list_id')
                    ->where('gettingstarted_todo_list_views.user_id', $context->entityId());
            })
            ->where('gettingstarted_todo_lists.resolution', $resolution)
            ->whereNull('gettingstarted_todo_list_views.id')
            ->orderBy('gettingstarted_todo_lists.ordering')
            ->limit(1);

        return $query->first();
    }

    protected function handleAttachedPhotos(User $context, Model $todoList, array $attachedPhotos, bool $isUpdate = false): void
    {
        resolve(TodoListImageRepositoryInterface::class)->updateImages(
            $context,
            $todoList->entityId(),
            $attachedPhotos,
            $isUpdate
        );
    }

    private function buildQuery(array $params): Builder
    {
        $query = $this->getModel()
            ->newModelQuery()
            ->select(['gettingstarted_todo_lists.*'])
            ->from('gettingstarted_todo_lists');

        if (isset($params['resolution'])) {
            $query->where('resolution', $params['resolution']);
        }

        return $query
            ->orderBy('gettingstarted_todo_lists.ordering');
    }
}
