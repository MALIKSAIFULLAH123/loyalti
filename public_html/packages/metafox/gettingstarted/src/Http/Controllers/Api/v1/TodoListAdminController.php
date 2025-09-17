<?php

namespace MetaFox\GettingStarted\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin\IndexRequest;
use MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin\StoreRequest;
use MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin\UpdateRequest;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin\StoreTodoListForm;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin\TodoListItemCollection;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin\UpdateTodoListForm;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\TodoListDetail;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class TodoListAdminController extends ApiController
{
    public function __construct(protected TodoListRepositoryInterface $repository)
    {
    }

    public function index(IndexRequest $request): TodoListItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewTodoListForAdminCP($context, $params);

        return new TodoListItemCollection($data);
    }

    public function create(): StoreTodoListForm
    {
        return new StoreTodoListForm();
    }

    public function store(StoreRequest $request)
    {
        $params = $request->validated();

        $context = user();

        $data   = $this->repository->createTodoListAdminCP($context, $params);

        Artisan::call('cache:reset');

        return $this->success(new TodoListDetail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/getting-started/todo-list/browse',
                ],
            ],
        ], __p('getting-started::phrase.todo_list_successfully_created'));
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->with(['images', 'descriptions'])->find($id);
        $form = new UpdateTodoListForm($item, true);

        return $this->success($form);
    }

    public function update(UpdateRequest $request, $id)
    {
        $params = $request->validated();

        $context = user();

        $data   = $this->repository->updateTodoListAdminCP($context, $id, $params);

        Artisan::call('cache:reset');

        return $this->success(new TodoListDetail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/getting-started/todo-list/browse',
                ],
            ],
        ], __p('getting-started::phrase.todo_list_successfully_updated'));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteTodoListAdminCP(user(), $id);

        Artisan::call('cache:reset');

        return $this->success([
            'id' => $id,
        ], [], __p('getting-started::phrase.todo_list_deleted_successfully'));
    }

    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderTodoList($orderIds);

        return $this->success([], [], __p('getting-started::phrase.todo_list_successfully_ordered'));
    }
}
