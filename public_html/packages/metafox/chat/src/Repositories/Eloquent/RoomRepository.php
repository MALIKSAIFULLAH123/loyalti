<?php

namespace MetaFox\Chat\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MetaFox\Chat\Broadcasting\RoomMessage;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Chat\Policies\RoomPolicy;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Chat\Repositories\RoomRepositoryInterface;
use MetaFox\Chat\Models\Room;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class RoomRepository.
 * @property Room $model
 * @method   Room getModel()
 * @method   Room find($id, $columns = ['*'])
 * @method   Room findByField($field, $value = null, $columns = ['*'])
 */
class RoomRepository extends AbstractRepository implements RoomRepositoryInterface
{
    public function model(): string
    {
        return Room::class;
    }

    public function viewRooms(User $context, User $owner, array $attributes): Paginator
    {
        $search = $attributes['q'] ?? '';
        $limit  = $attributes['limit'];

        $query = $this->getModel()->newQuery();

        $latestMessagesSubquery = DB::table('chat_messages')
            ->select('room_id', DB::raw('MAX(created_at) as latest_message_at'))
            ->groupBy('room_id');

        $query = $query
            ->with(['subscriptions'])
            ->join('chat_subscriptions as cs', function (JoinClause $join) use ($context) {
                $join->on('cs.room_id', '=', 'chat_rooms.id')
                    ->where([
                        ['cs.user_id', '=', $context->entityId()],
                        ['cs.is_showed', '=', 1],
                    ]);
            })
            ->leftJoinSub($latestMessagesSubquery, 'lm', 'lm.room_id', '=', 'chat_rooms.id');

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['cs.name']));
        }

        return $query
            ->orderByDesc('lm.latest_message_at')
            ->orderByDesc('cs.updated_at')
            ->simplePaginate($limit, ['chat_rooms.*', 'cs.total_unseen', 'cs.name', 'cs.updated_at']);
    }

    public function viewRoom(User $context, int $id): Room
    {
        $query = $this->getModel()->newQuery()
            ->with(['subscriptions']);

        $query = $query
            ->join('chat_subscriptions as cs', function (JoinClause $join) use ($context) {
                $join->on('cs.room_id', '=', 'chat_rooms.id')
                    ->where([
                        ['cs.user_id', '=', $context->entityId()],
                    ]);
            });

        $room = $query->select('chat_rooms.*')->find($id);

        if ($room instanceof Room) {
            policy_authorize(RoomPolicy::class, 'view', $context, $room);
        }

        return $room;
    }

    public function createChatRoom(User $context, array $attributes): Room
    {
        $memberIds   = array_map('intval', $attributes['member_ids']);
        $memberIds[] = $context->entityId();
        sort($memberIds);
        $uid = md5(json_encode($memberIds));

        $checkedRoom = $this->findByField('uid', $uid)->first();
        if (!empty($checkedRoom)) {
            $subscription =  Subscription::query()
                ->where('user_id', '=', $context->entityId())
                ->where('room_id', '=', $checkedRoom->id)
                ->first();
            if ($subscription->is_deleted == 1) {
                $subscription->update([
                    'is_deleted' => 0,
                    'rejoin_at'  => Carbon::now(),
                ]);
            } else {
                $subscription->update([
                    'is_deleted' => 0,
                ]);
            }
            $subscription->touch('updated_at');

            return $checkedRoom;
        }

        $attributes = array_merge($attributes, [
            'uid'         => $uid,
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $context->ownerId(),
            'owner_type'  => $context->ownerType(),
            'is_archived' => 0,
            'is_readonly' => 0,
            'type'        => 'd', //direct chat type
        ]);

        $room = new Room($attributes);
        $room->save();
        $room->refresh();

        resolve(SubscriptionRepositoryInterface::class)->massCreateMemberSubscription($context, $room->id, $memberIds);

        return $room;
    }

    public function deleteRoom(User $user, int $id): int
    {
        $room = $this->find($id);

        policy_authorize(RoomPolicy::class, 'delete', $user, $room);

        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($id, false, $user->entityId());
        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            broadcast(new RoomMessage($id, $userId, Room::ROOM_DELETED));
        }

        return Subscription::query()
            ->where('user_id', '=', $user->entityId())
            ->where('room_id', '=', $id)
            ->update([
                'total_unseen' => 0,
                'is_deleted'   => 1,
                'is_showed'    => 0,
            ]);
    }
}
