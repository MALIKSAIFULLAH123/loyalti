<?php

namespace MetaFox\Quiz\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Quiz\Http\Requests\v1\Quiz\IndexRequest;
use MetaFox\Quiz\Http\Requests\v1\Quiz\StoreRequest;
use MetaFox\Quiz\Http\Requests\v1\Quiz\UpdateRequest;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizDetail;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizDetail as Detail;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizItemCollection as ItemCollection;
use MetaFox\Quiz\Http\Resources\v1\Quiz\SearchQuizForm as SearchForm;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 * Assign this class in $controllers of
 *
 * @link \MetaFox\Quiz\Http\Controllers\Api\QuizController::$controllers;
 */

/**
 * Class QuizController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuizController extends ApiController
{
    /**
     * @var QuizRepositoryInterface
     */
    public QuizRepositoryInterface $repository;

    public function __construct(QuizRepositoryInterface $repository)
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
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = $owner = user();
        $params  = $request->validated();
        $view    = Arr::get($params, 'view');
        $limit   = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
            policy_authorize(QuizPolicy::class, 'viewOnProfilePage', $context, $owner);
        }
        policy_authorize(QuizPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewQuizzes($context, $owner, $params),
        };

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();
        $params  = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem(user(), Quiz::ENTITY_TYPE);
        app('quota')->checkQuotaControlWhenCreateItem(user(), Quiz::ENTITY_TYPE);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $quiz = $this->repository->createQuiz($context, $owner, $params);

        $message = __p('quiz::phrase.quiz_successfully_created');

        $ownerPendingMessage = $quiz->getOwnerPendingMessage();

        if (null !== $ownerPendingMessage) {
            $message = $ownerPendingMessage;
        }
        $meta = $this->repository->askingForPurchasingSponsorship($context, $quiz);

        return $this->success(new Detail($quiz), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewQuiz(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateQuiz(user(), $id, $params);

        return $this->success(
            new Detail($data),
            [],
            __p('quiz::phrase.quiz_updated_successfully')
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteQuiz(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('quiz::phrase.quiz_deleted_successfully'));
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
        $message          = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message          = __p($message, ['resource_name' => __p('quiz::phrase.quiz')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($quiz), [], $message);
    }

    /**
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $feature = (int) $params['feature'];
        $context = user();

        match ($feature) {
            1       => $this->repository->featureFree($context, $id),
            default => $this->repository->unfeature($context, $id),
        };

        $message = __p('quiz::phrase.quiz_featured_succesfully');
        if (!$feature) {
            $message = __p('quiz::phrase.quiz_unfeatured_succesfully');
        }

        $quiz = $this->repository->find($id);

        return $this->success(new Detail($quiz), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $resource = $this->repository->approve(user(), $id);

        // @todo recheck response.
        return $this->success(new QuizDetail($resource), [], __p('quiz::phrase.quiz_has_been_approved'));
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

        $message = __p($message, ['resource_name' => __p('quiz::phrase.quiz')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($quiz), [], $message);
    }

    /**
     * @return JsonResponse
     * @todo Need working with policy + repository later
     */
    public function searchForm(): JsonResponse
    {
        return $this->success(new SearchForm([]), [], '');
    }
}
