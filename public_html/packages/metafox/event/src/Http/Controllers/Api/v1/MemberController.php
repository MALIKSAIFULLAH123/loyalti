<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Event\Http\Requests\v1\Member\DeleteRequest;
use MetaFox\Event\Http\Requests\v1\Member\IndexRequest;
use MetaFox\Event\Http\Requests\v1\Member\InterestRequest;
use MetaFox\Event\Http\Requests\v1\Member\LeaveRequest;
use MetaFox\Event\Http\Requests\v1\Member\StoreRequest;
use MetaFox\Event\Http\Resources\v1\Event\EventDetail;
use MetaFox\Event\Http\Resources\v1\Member\MemberItemCollection as ItemCollection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class MemberController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MemberController extends ApiController
{
    public function __construct(
        public MemberRepositoryInterface $repository,
        public EventRepositoryInterface $eventRepository,
        public InviteCodeRepositoryInterface $inviteCodeRepository,
    ) {
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
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $event  = $this->eventRepository->find($params['event_id']);
        $data   = $this->repository->viewEventMembers($event, user(), $params);

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
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $event   = $this->eventRepository->find($params['event_id']);
        $code    = Arr::get($params, 'invite_code');
        if ($code !== null) {
            $inviteCode = $this->inviteCodeRepository->getCodeByValue($params['invite_code'], InviteCode::STATUS_ACTIVE);
            if ($inviteCode == null) {
                $message = json_encode([
                    'title'   => __p('core::phrase.the_link_you_followed_has_expired_title'),
                    'message' => __p('core::phrase.the_link_you_followed_has_expired'),
                ]);
                abort(403, $message);
            }
        }
        $member = $this->repository->joinEvent($event, $context, Member::ROLE_MEMBER);

        if ($member) {
            return $this->success(ResourceGate::asResource($member, 'detail'), [], __p('event::phrase.joined_successfully'));
        }

        return $this->success([]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int          $id
     * @param LeaveRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id, LeaveRequest $request): JsonResponse
    {
        $params = $request->validated();
        $event  = $this->eventRepository->find($id);
        $result = $this->repository->leaveEvent($event, user(), (bool) $params['not_invite_again']);

        return $this->success($result);
    }

    /**
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeMember(DeleteRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $eventId = $params['event_id'];
        $userId  = $params['user_id'];

        $this->repository->deleteMember($context, $eventId, $userId);

        $event = $this->eventRepository->find($eventId);

        return $this->success([
            'id'               => (int) $eventId,
            'total_member'     => $event->total_member,
            'total_interested' => $event->total_interested,
        ], [], __p('event::phrase.remove_event_member_successfully'));
    }

    /**
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeHost(DeleteRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $eventId = $params['event_id'];

        $this->repository->removeHost($context, $eventId, $params['user_id']);

        /** @var Event $event */
        $event    = $this->eventRepository->find($params['event_id']);
        $resource = new EventDetail($event->refresh());

        return $this->success($resource, [], __p('event::phrase.remove_event_host_successfully'));
    }

    /**
     * @param InterestRequest $request
     * @param int             $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function interest(InterestRequest $request, int $id): JsonResponse
    {
        $params       = $request->validated();
        $isInterested = (bool) $params['interest'];

        $event = $this->eventRepository->find($id);

        $code = Arr::get($params, 'invite_code');
        if ($code !== null) {
            $inviteCode = $this->inviteCodeRepository->getCodeByValue($params['invite_code'], InviteCode::STATUS_ACTIVE);
            if ($inviteCode == null) {
                $message = json_encode([
                    'title'   => __p('core::phrase.the_link_you_followed_has_expired_title'),
                    'message' => __p('core::phrase.the_link_you_followed_has_expired'),
                ]);
                abort(403, $message);
            }
        }
        $member = match ($isInterested) {
            true  => $this->repository->setInterestedInEvent($event, user()),
            false => $this->repository->setNotInterestedInEvent($event, user()),
        };

        if ($member) {
            return $this->success(ResourceGate::asResource($member, 'detail'), [], __p('event::phrase.event_interest_updated'));
        }

        return $this->success([]);
    }
}
