<?php

namespace MetaFox\Hashtag\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Hashtag\Http\Requests\v1\Hashtag\IndexRequest;
use MetaFox\Hashtag\Http\Requests\v1\Hashtag\SuggestionRequest;
use MetaFox\Hashtag\Http\Resources\v1\Hashtag\HashtagItemCollection as ItemCollection;
use MetaFox\Hashtag\Repositories\TagRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Hashtag\Http\Controllers\Api\HashtagController::$controllers;
 */

/**
 * Class HashtagController.
 */
class HashtagController extends ApiController
{
    public TagRepositoryInterface $repository;

    public function __construct(TagRepositoryInterface $repository)
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
        $data = $this->repository->viewHashtags(user(), $request->validated());

        return $this->success(new ItemCollection($data), [], '');
    }

    /**
     * Display a listing of the resource.
     *
     * @param SuggestionRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function suggestion(SuggestionRequest $request): JsonResponse
    {
        $data = $this->repository->suggestionHashtags(user(), $request->validated());

        return $this->success($data);
    }
}
