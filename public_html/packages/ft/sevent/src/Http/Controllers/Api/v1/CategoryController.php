<?php

namespace Foxexpert\Sevent\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Foxexpert\Sevent\Http\Requests\v1\Category\IndexRequest;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class CategoryController.
 * @ignore
 * @codeCoverageIgnore
 * @group sevent
 * @authenticated
 */
class CategoryController extends ApiController
{
    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $repository;

    /**
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
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
