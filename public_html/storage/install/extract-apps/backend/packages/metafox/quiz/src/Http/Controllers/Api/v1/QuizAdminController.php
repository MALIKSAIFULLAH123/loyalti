<?php

namespace MetaFox\Quiz\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Quiz\Http\Requests\v1\Quiz\Admin\BatchUpdateRequest;
use MetaFox\Quiz\Http\Requests\v1\Quiz\Admin\IndexRequest;
use MetaFox\Quiz\Http\Resources\v1\Quiz\Admin\QuizItemCollection as ItemCollection;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizAdminRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Quiz\Http\Controllers\Api\QuizAdminController::$controllers;
 */

/**
 * Class QuizAdminController
 *
 * Handles administrative operations for quizzes.
 *
 * @extends ApiController
 */
class QuizAdminController extends ApiController
{
    public function __construct(protected QuizAdminRepositoryInterface $repository) {}

    /**
     * @param IndexRequest $request
     * @return JsonResource
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $params  = $request->validated();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $context = user();

        policy_authorize(QuizPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewQuizzes($context, $params)
            ->paginate($limit, ['quizzes.*']);

        return new ItemCollection($data);
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];
        $this->repository->sponsor(user(), $id, $sponsor);

        $quiz             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$quiz->is_sponsor;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('quiz::phrase.quiz')]));
    }

    /**
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $quiz             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$quiz->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('quiz::phrase.quiz')]));
    }

    /**
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchApprove(BatchUpdateRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $model = $this->repository->find($id);

            if ($model->isApproved()) {
                continue;
            }

            $this->repository->approve(user(), $id);
        }

        return $this->success([], [], __p('quiz::phrase.quiz_s_has_been_approved'));
    }

    /**
     *
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        if (!user()->hasPermissionTo('quiz.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Quiz $model) {
                $model->delete();
            });

        return $this->success([], [], __p('quiz::phrase.quiz_s_deleted_successfully'));
    }
}
