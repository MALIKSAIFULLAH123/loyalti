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
use MetaFox\Event\Http\Resources\v1\Event\EventDetail as Detail;
use MetaFox\Event\Http\Resources\v1\HostInvite\InviteItemCollection as ItemCollection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class HostInviteController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 */
class HostInviteController extends ApiController
{
    public HostInviteRepositoryInterface $repository;
    public EventRepositoryInterface $eventRepository;

    public function __construct(HostInviteRepositoryInterface $repository, EventRepositoryInterface $eventRepository)
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
        /** @var Event $event */
        $event   = $this->eventRepository->find($params['event_id']);
        $context = user();
        $this->repository->inviteHosts($context, $event, $params['user_ids']);

        $data = new EventDetail($event->refresh());

        return $this->success($data, [], __p('core::phrase.invitation_s_successfully_sent'));
    }

    /**
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        /** @var Event $event */
        $params   = $request->validated();
        $event    = $this->eventRepository->find($params['event_id']);
        $context  = user();
        $isAccept = (int) $params['accept'];

        if ($isAccept) {
            $result = $this->repository->acceptInvite($event, $context);
        }

        if (!$isAccept) {
            $result = $this->repository->declineInvite($event, $context);
        }

        if (!$result) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'), 403);
        }

        $message = $isAccept ? __p('event::phrase.host_invitation_accepted') :
            __p('event::phrase.denied_successfully');

        return $this->success(new Detail($event->refresh()), [], $message);
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
        $this->repository->deleteInvite(user(), $params['event_id'], $params['user_id']);

        /** @var Event $event */
        $event    = $this->eventRepository->find($params['event_id']);
        $resource = new EventDetail($event->refresh());

        return $this->success($resource, [], __p('event::phrase.invitation_successfully_deleted'));
    }
}
