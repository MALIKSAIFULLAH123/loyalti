<?php

namespace MetaFox\Chat\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Chat\Http\Requests\v1\Room\IndexRequest;
use MetaFox\Chat\Http\Requests\v1\Room\MarkAllReadRequest;
use MetaFox\Chat\Http\Requests\v1\Room\StoreRequest;
use MetaFox\Chat\Http\Resources\v1\Room\CreateChatRoomForm;
use MetaFox\Chat\Http\Resources\v1\Room\RoomDetail;
use MetaFox\Chat\Http\Resources\v1\Room\RoomItemCollection;
use MetaFox\Chat\Repositories\RoomRepositoryInterface;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class ChatRoomController extends ApiController
{
    /**
     * @var RoomRepositoryInterface
     */
    private RoomRepositoryInterface $repository;

    private SubscriptionRepositoryInterface $subscriptionRepository;

    /**
     * @param RoomRepositoryInterface         $repository
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        RoomRepositoryInterface $repository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->repository             = $repository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(IndexRequest $request)
    {
        $params  = $request->validated();
        $context = user();
        $owner   = $context;

        $data = $this->repository->viewRooms($context, $owner, $params);

        return $this->success(new RoomItemCollection($data));
    }

    public function show(int $id)
    {
        $room = $this->repository->viewRoom(user(), $id);

        if (null == $room) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => __p('chat::phrase.chat_room_lower')]),
                403
            );
        }

        return new RoomDetail($room);
    }

    public function store(StoreRequest $request)
    {
        $context = user();

        $params = $request->validated();
        $room   = $this->repository->createChatRoom($context, $params);

        return $this->success(new RoomDetail($room));
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function destroy(int $id)
    {
        $context = user();
        $this->repository->deleteRoom($context, $id);

        return $this->success([
            'id' => $id,
        ]);
    }

    public function formCreateRoom()
    {
        $form = new CreateChatRoomForm();

        return $this->success($form);
    }

    public function markRead(int $id)
    {
        $context = user();
        $this->subscriptionRepository->markRead($context, $id);

        $room = $this->repository->viewRoom(user(), $id);

        return new RoomDetail($room);
    }

    public function markAllRead(MarkAllReadRequest $request)
    {
        $params  = $request->validated();
        $context = user();
        $this->subscriptionRepository->markAllRead($context, $params);

        return $this->success([], [], __p('chat::phrase.mark_all_read_successfully'));
    }
}
