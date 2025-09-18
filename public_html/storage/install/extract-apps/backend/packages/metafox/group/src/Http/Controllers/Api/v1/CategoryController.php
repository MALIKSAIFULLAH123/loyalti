<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Requests\v1\Category\IndexRequest;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class CategoryController.
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class CategoryController extends ApiController
{
    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $repository;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse category.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $data = $this->repository->getStructure(user(), $request->validated());

        return $this->success($data);
    }
}
