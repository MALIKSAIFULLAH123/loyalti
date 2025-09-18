<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\StoryReaction\StoreRequest;
use MetaFox\Story\Http\Resources\v1\Story\StoryDetail as Detail;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;
use MetaFox\Story\Repositories\StoryRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\StoryReactionController::$controllers;
 */

/**
 * Class StoryReactionController
 * @codeCoverageIgnore
 * @ignore
 */
class StoryReactionController extends ApiController
{
    /**
     * StoryReactionController Constructor
     *
     * @param StoryReactionRepositoryInterface $repository
     * @param StoryRepositoryInterface         $storyRepository
     */
    public function __construct(
        protected StoryReactionRepositoryInterface $repository,
        protected StoryRepositoryInterface         $storyRepository
    ) {}

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): Detail
    {
        $params  = $request->validated();
        $storyId = Arr::get($params, 'story_id');
        $story   = $this->storyRepository->find($storyId);
        $context = user();

        if ($story->isExpired()) {
            abort(403, json_encode([
                'title'   => __p('story::phrase.story_expired_error_title'),
                'message' => __p('story::phrase.story_expired_error_message'),
            ]));
        }

        policy_authorize(StoryPolicy::class, 'view', $context, $story);

        $data = $this->repository->createReaction($context, $story, $params);

        return new Detail($data);
    }
}
