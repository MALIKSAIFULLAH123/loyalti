<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Event\Http\Requests\v1\Invite\DeleteRequest;
use MetaFox\Event\Http\Requests\v1\Invite\IndexRequest;
use MetaFox\Event\Http\Requests\v1\Invite\StoreRequest;
use MetaFox\Event\Http\Requests\v1\Invite\UpdateRequest;
use MetaFox\Event\Http\Resources\v1\Event\EventDetail;
use MetaFox\Event\Http\Resources\v1\Invite\InviteItemCollection as ItemCollection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class InviteController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 */
class InviteController extends ApiController
{
    public InviteRepositoryInterface $repository;
    public EventRepositoryInterface  $eventRepository;

    public function __construct(InviteRepositoryInterface $repository, EventRepositoryInterface $eventRepository)
    {
        $this->repository      = $repository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params  = $request->validated();
        $results = $this->repository->viewInvites(user(), $params);

        return $this->success(new ItemCollection($results));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException|ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $event   = $this->eventRepository->find($params['event_id']);

        policy_authorize(EventPolicy::class, 'invite', $context, $event);

        $this->repository->inviteFriends(user(), $params['event_id'], $params['user_ids']);

        $resource = new EventDetail($event->refresh());

        return $this->success($resource, [], __p('core::phrase.invitation_s_successfully_sent'));
    }

    /**
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $event    = $this->eventRepository->find($params['event_id']);
        $context  = user();
        $isAccept = (int)$params['accept'];

        if ($isAccept) {
            $result = $this->repository->acceptInvite($event, $context);
        }

        if (!$isAccept) {
            $result = $this->repository->declineInvite($event, $context);
        }

        if (false == $result) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'), 403);
        }

        if ($isAccept) {
            return $this->success([
                'id'           => $event->entityId(),
                'total_member' => $event->refresh()->total_member,
                'membership'   => Member::JOINED,
            ], [], __p('event::phrase.joined_successfully'));
        }

        return $this->success([
            'id'           => $event->entityId(),
            'total_member' => $event->total_member,
            'membership'   => Member::NOT_INTERESTED,
        ], [], __p('event::phrase.denied_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function delete(DeleteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->deleteEventInvite(user(), $params['event_id'], $params['user_id']);

        /** @var Event $event */
        $event    = $this->eventRepository->find($params['event_id']);
        $resource = new EventDetail($event->refresh());

        return $this->success($resource, [], __p('event::phrase.invitation_successfully_deleted'));
    }
}
