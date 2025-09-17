<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Event\Http\Requests\v1\Event\IndexRequest;
use MetaFox\Event\Http\Requests\v1\Event\MassEmailRequest;
use MetaFox\Event\Http\Requests\v1\Event\ShowRequest;
use MetaFox\Event\Http\Requests\v1\Event\StoreRequest;
use MetaFox\Event\Http\Requests\v1\Event\UpdateBannerRequest;
use MetaFox\Event\Http\Requests\v1\Event\UpdateRequest;
use MetaFox\Event\Http\Resources\v1\Event\EventDetail;
use MetaFox\Event\Http\Resources\v1\Event\EventDetail as Detail;
use MetaFox\Event\Http\Resources\v1\Event\EventItemCollection as ItemCollection;
use MetaFox\Event\Http\Resources\v1\Event\EventStatDetail;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Event\Support\Facades\Exporter;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Event\Http\Controllers\Api\EventController::$controllers.
 */

/**
 * Class EventController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventController extends ApiController
{
    /**
     * @var EventRepositoryInterface
     */
    public $repository;

    public function __construct(EventRepositoryInterface $repository, protected InviteRepositoryInterface $inviteRepository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $owner  = $context = user();
        $view   = Arr::get($params, 'view');
        $limit  = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;

            policy_authorize(EventPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(EventPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewEvents($context, $owner, $params),
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
     * @throws PermissionDeniedException
     * @throws ValidatorException
     */
    public function store(StoreRequest $request)
    {
        $owner  = $context = user();
        $params = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem(user(), Event::ENTITY_TYPE);
        app('quota')->checkQuotaControlWhenCreateItem(user(), Event::ENTITY_TYPE);
        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $event = $this->repository->createEvent($context, $owner, $params);

        $message = __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('event::phrase.event')]
        );

        $ownerPendingMessage = $event->getOwnerPendingMessage();

        if (null !== $ownerPendingMessage) {
            $message = $ownerPendingMessage;
        }

        $meta = $this->repository->askingForPurchasingSponsorship($context, $event);

        return $this->success(new Detail($event), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @param int         $id
     *
     * @return Detail
     */
    public function show(ShowRequest $request, int $id)
    {
        $params = $request->validated();
        $event  = $this->repository->getEvent(user(), $id);

        $resource = new Detail($event);
        if (isset($params['invite_code'])) {
            $resource->setInviteCode($params['invite_code']);
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
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, $id)
    {
        $params = $request->validated();

        $data = $this->repository->updateEvent(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('event::phrase.event_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $this->repository->deleteEvent(user(), $id);

        return $this->success(['id' => $id], [], __p('event::phrase.event_deleted_successfully'));
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $context = user();

        $this->repository->sponsor($context, $id, $sponsor);

        $event = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$event->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');

        $message = __p($message, ['resource_name' => __p('event::phrase.event')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($event), [], $message);
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
            1       => __p('event::phrase.event_featured_successfully'),
            default => __p('event::phrase.event_unfeatured_successfully'),
        };

        $event = $this->repository->find($id);

        return $this->success(new Detail($event), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $event = $this->repository->approve(user(), $id);
        $this->repository->handleSendInviteNotification($id);

        return $this->success(new Detail($event), [], __p('event::phrase.event_has_been_approved'));
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getStats(int $id): JsonResponse
    {
        $event = $this->repository->find($id);

        $context = user();

        policy_authorize(EventPolicy::class, 'view', $context, $event);

        return $this->success(new EventStatDetail($event));
    }

    /**
     * @throws AuthenticationException
     */
    public function massEmail(MassEmailRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $this->repository->massEmail($context, $id, $params);

        return $this->success([], [], __p('event::phrase.email_sent_successfully'));
    }

    /**
     * Sponsor event in feed.
     *
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $isSponsor        = (bool) $sponsor;
        $event            = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$event->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        $message = __p($message, ['resource_name' => __p('event::phrase.event')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($event), [], $message);
    }

    /**
     * @param UpdateBannerRequest $request
     * @param int                 $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateBanner(UpdateBannerRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data         = $this->repository->updateBanner(user(), $id, $params);
        $data['user'] = new EventDetail($data['user']);

        return $this->success($data, [], __p('event::phrase.successfully_updated_banner_event'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function removeBanner(int $id): JsonResponse
    {
        $this->repository->removeBanner(user(), $id);

        return $this->success([], [], __p('event::phrase.banner_event_removed_successfully'));
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @throws ValidatorException
     */
    public function massInvite(int $id): JsonResponse
    {
        $context = user();
        $event   = $this->repository->find($id);

        policy_authorize(EventPolicy::class, 'massInvite', $context, $event);

        if (!$event->hasMassInviteCooldown()) {
            abort(403, json_encode([
                'message' => __p('event::phrase.event_invite_not_allowed', [
                    'second' => $event->getMassInviteCooldown(),
                ]),
                'title'   => __p('core::phrase.oops'),
            ]));
        }

        $this->inviteRepository->inviteFriends($context, $id, []);

        $event    = $this->repository->updateLastMassInvite($context, $event);
        $resource = new EventDetail($event->refresh());

        return $this->success($resource, [], __p('core::phrase.invitation_s_successfully_sent'));
    }

    /**
     * @param int $id
     *
     * @return BinaryFileResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function export(int $id): BinaryFileResponse
    {
        $event   = $this->repository->find($id);
        $context = user();

        policy_authorize(EventPolicy::class, 'export', $context, $event);

        $data      = Exporter::export($context, $event);
        $fileName  = Exporter::getFileName($context->entityId());
        $exportUrl = Exporter::putFile($fileName, $data);

        return response()->download($exportUrl, 'export_event.ics')->deleteFileAfterSend();
    }
}
