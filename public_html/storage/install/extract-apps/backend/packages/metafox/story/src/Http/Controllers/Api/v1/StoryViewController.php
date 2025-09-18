<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\StoryView\IndexRequest;
use MetaFox\Story\Http\Requests\v1\StoryView\StoreRequest;
use MetaFox\Story\Http\Resources\v1\StoryView\StoryViewItemCollection as ItemCollection;
use MetaFox\Story\Repositories\StoryViewRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\StoryViewController::$controllers;.
 */

/**
 * Class StoryViewController.
 * @codeCoverageIgnore
 * @ignore
 */
class StoryViewController extends ApiController
{
    /**
     * StoryViewController Constructor.
     *
     * @param StoryViewRepositoryInterface $repository
     */
    public function __construct(protected StoryViewRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $storyId = Arr::get($params, 'story_id');

        $data = $this->repository->viewStoryViewers(user(), $storyId, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->createViewer(user(), $params);

        return $this->success(['has_seen' => true]);
    }
}
