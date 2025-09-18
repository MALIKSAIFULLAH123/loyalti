<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Music\Http\Requests\v1\Genre\IndexRequest;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class GenreController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 */
class GenreController extends ApiController
{
    /**
     * @var GenreRepositoryInterface
     */
    private GenreRepositoryInterface $repository;

    /**
     * @param GenreRepositoryInterface $repository
     */
    public function __construct(GenreRepositoryInterface $repository)
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
        $data =  $this->repository->getStructure(user(), $request->validated());

        return $this->success($data);
    }
}
