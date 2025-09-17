<?php

namespace MetaFox\GettingStarted\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\GettingStarted\Http\Requests\v1\TodoList\IndexRequest;
use MetaFox\GettingStarted\Http\Requests\v1\TodoList\MarkRequest;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\TodoListItem;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\TodoListItemCollection as ItemCollection;
use MetaFox\GettingStarted\Http\Resources\v1\TodoList\TodoListDetail as Detail;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\GettingStarted\Repositories\TodoListViewRepositoryInterface;
use MetaFox\GettingStarted\Support\Traits\GettingStartedTrait;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class TodoListController extends ApiController
{
    use GettingStartedTrait;

    public function __construct(
        public TodoListRepositoryInterface $todoListRepository,
        public TodoListViewRepositoryInterface $todoListViewRepository,
    ) {
    }

    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->todoListRepository->viewTodoList($context, $params);

        $this->markFirstLogin($context);

        $resources = new ItemCollection($data);

        $responseData = $resources->toResponse($request)->getData(true);

        $meta = Arr::get($responseData, 'meta', []);

        return $this->success($resources, $meta);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->todoListRepository->viewTodoListDetail(user(), $id);

        return $this->success(new Detail($data));
    }

    public function mark(MarkRequest $request)
    {
        $params = $request->validated();

        $context = user();

        $this->todoListViewRepository->markDone($context, $params);

        return $this->success(null, [], '');
    }
}
