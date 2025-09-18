<?php

namespace MetaFox\Video\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Video\Http\Requests\v1\Category\IndexRequest;
use MetaFox\Video\Http\Requests\v1\Category\StoreRequest;
use MetaFox\Video\Http\Resources\v1\Category\CategoryDetail;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryController.
 * @ignore
 * @codeCoverageIgnore
 * @group video
 * @authenticated
 */
class CategoryController extends ApiController
{
    public CategoryRepositoryInterface $repository;

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

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $category = $this->repository->createCategory(user(), $request->validated());

        return $this->success(new CategoryDetail($category), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/video/category/browse',
                ],
            ],
        ], __p('core::phrase.resource_create_success', [
            'resource_name' => __p('core::phrase.category'),
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->repository->viewCategory(user(), $id);

        return $this->success(new CategoryDetail($category));
    }
}
