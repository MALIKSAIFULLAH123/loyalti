<?php

namespace MetaFox\Poll\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\IntegrationRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Poll\Http\Requests\v1\Poll\IndexRequest;
use MetaFox\Poll\Http\Requests\v1\Poll\StoreRequest;
use MetaFox\Poll\Http\Requests\v1\Poll\UpdateRequest;
use MetaFox\Poll\Http\Resources\v1\Poll\IntegrationCreatePollForm;
use MetaFox\Poll\Http\Resources\v1\Poll\PollDetail;
use MetaFox\Poll\Http\Resources\v1\Poll\PollDetail as Detail;
use MetaFox\Poll\Http\Resources\v1\Poll\PollItemCollection as ItemCollection;
use MetaFox\Poll\Http\Resources\v1\Poll\SearchPollForm as SearchForm;
use MetaFox\Poll\Http\Resources\v1\Poll\StatusCreatePollForm;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 * Assign this class in $controllers of
 *
 * @link \MetaFox\Poll\Http\Controllers\Api\PollController::$controllers;
 */

/**
 * Class PollController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PollController extends ApiController
{
    /**
     * @var PollRepositoryInterface
     */
    public PollRepositoryInterface $repository;

    /**
     * PollController constructor.
     *
     * @param PollRepositoryInterface $repository
     */
    public function __construct(PollRepositoryInterface $repository)
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
        $params = $request->validated();
        $owner  = $context = user();
        $view   = Arr::get($params, 'view');
        $limit  = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
            policy_authorize(PollPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(PollPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewPolls($context, $owner, $params),
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
     * @throws ValidationException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $owner  = $context = user();
        $params = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem(user(), Poll::ENTITY_TYPE);
        app('quota')->checkQuotaControlWhenCreateItem(user(), Poll::ENTITY_TYPE);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $poll = $this->repository->createPoll($context, $owner, $params);

        $message = __p('poll::phrase.poll_created_successfully');

        $ownerPendingMessage = $poll->getOwnerPendingMessage();

        if (null !== $ownerPendingMessage) {
            $message = $ownerPendingMessage;
        }
        $meta = $this->repository->askingForPurchasingSponsorship($context, $poll);

        return $this->success(new Detail($poll), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewPoll(user(), $id);

        /**
         * @var Detail $resource
         */
        $resource = ResourceGate::asDetail($data, null);

        if (null === $resource) {
            throw new NotFoundHttpException();
        }

        return $resource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException | AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $data = $this->repository->updatePoll(user(), $id, $request->validated());

        return $this->success(new Detail($data), [], __p('poll::phrase.poll_updated_successfully'));
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
        $this->repository->deletePoll(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('poll::phrase.poll_deleted_successfully'));
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

        $poll             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$poll->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('poll::phrase.poll')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($poll), [], $message);
    }

    /**
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
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

        $message = match ($feature) {
            1       => __p('poll::phrase.poll_featured_successfully'),
            default => __p('poll::phrase.poll_unfeatured_successfully'),
        };

        $poll = $this->repository->find($id);

        return $this->success(new Detail($poll), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $poll = $this->repository->approve(user(), $id);

        // @todo recheck response.
        return $this->success(new PollDetail($poll), [], __p('poll::phrase.poll_has_been_approved'));
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

        $poll             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$poll->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');
        $message          = __p($message, ['resource_name' => __p('poll::phrase.poll')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($poll), [], $message);
    }

    /**
     * @return JsonResponse
     */
    public function searchForm(): JsonResponse
    {
        return $this->success(new SearchForm([]), [], '');
    }

    /**
     * @return JsonResponse
     */
    public function statusForm(Request $request): JsonResponse
    {
        $isEdit = (bool) $request->get('is_edit') ?? false;

        return $this->success(new StatusCreatePollForm(null, $isEdit));
    }

    /**
     * @return JsonResponse
     */
    public function integrationForm(IntegrationRequest $request): JsonResponse
    {
        $params = $request->validated();

        $pollId = Arr::get($params, 'poll_id');

        $poll = null;

        if (is_numeric($pollId)) {
            $poll = $this->repository->find($pollId);
        }

        $form = new IntegrationCreatePollForm($poll, (bool) Arr::get($params, 'is_edit', false));

        $form->boot();

        return $this->success($form);
    }
}
