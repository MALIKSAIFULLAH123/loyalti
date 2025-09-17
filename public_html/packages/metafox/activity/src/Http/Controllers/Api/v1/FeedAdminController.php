<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Activity\Http\Requests\v1\Feed\Admin\IndexRequest;
use MetaFox\Activity\Http\Resources\v1\Feed\Admin\FeedItemCollection;
use MetaFox\Activity\Repositories\FeedAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Activity\Http\Controllers\Api\FeedController::$controllers;
 */

/**
 * Class FeedAdminController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @group feed
 * @authenticated
 * @ignore
 * @codeCoverageIgnore
 */
class FeedAdminController extends ApiController
{
    /**
     * @param FeedAdminRepositoryInterface $feedAdminRepository
     */
    public function __construct(protected FeedAdminRepositoryInterface $feedAdminRepository)
    {
    }

    /**
     * Browse feed item.
     *
     * @param IndexRequest $request
     *
     * @return FeedItemCollection
     */
    public function index(IndexRequest $request): FeedItemCollection
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->feedAdminRepository->viewFeeds($context, $params);

        return new FeedItemCollection($data);
    }

    /**
     * Delete feed item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->feedAdminRepository->deleteFeed(user(), $id);
        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(['id' => $id], [], __p('activity::phrase.feed_deleted_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function deleteWithItems(int $id): JsonResponse
    {
        $response = $this->feedAdminRepository->deleteFeedWithItems(user(), $id);

        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(['id' => $id], [], __p('activity::phrase.feed_deleted_post_with_items_successfully'));
    }
}
