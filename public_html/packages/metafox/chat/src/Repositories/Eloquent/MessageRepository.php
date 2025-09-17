<?php

namespace MetaFox\Chat\Repositories\Eloquent;

use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Chat\Broadcasting\RoomMessage;
use MetaFox\Chat\Broadcasting\UserNotificationMessage;
use MetaFox\Chat\Http\Resources\v1\Message\ReplyMessageDetail;
use MetaFox\Chat\Jobs\DeleteOrMoveReactionJob;
use MetaFox\Chat\Jobs\MessageQueueJob;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Chat\Notifications\NewMessageNotification;
use MetaFox\Chat\Policies\MessagePolicy;
use MetaFox\Chat\Policies\RoomPolicy;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Chat\Repositories\UserNotificationRepositoryInterface;
use MetaFox\Chat\Support\Helper;
use MetaFox\Core\Models\Attachment;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Chat\Models\Message;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Http\Resources\v1\User\UserSimple;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class MessageRepository.
 */
class MessageRepository extends AbstractRepository implements MessageRepositoryInterface
{
    public function model(): string
    {
        return Message::class;
    }

    public function viewMessages(User $context, array $attributes): Paginator
    {
        $limit     = $attributes['limit'];
        $search    = $attributes['q'];
        $msgId     = $attributes['message_id'];
        $lastMsgId = $attributes['last_message_id'];
        $roomId    = Arr::get($attributes, 'room_id');

        if (!$roomId) {
            throw new AuthorizationException();
        }

        $room = Room::query()->getModel()
            ->with(['subscriptions'])
            ->find($attributes['room_id']);

        policy_authorize(RoomPolicy::class, 'view', $context, $room);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->where('chat_messages.type', '<>', 'delete');
            $query = $query->addScope(new SearchScope($search, ['message']));
        }

        if (!empty($lastMsgId)) {
            $query = $query->where('chat_messages.id', '<', $lastMsgId);
        }

        $query = $query->where('room_id', '=', $attributes['room_id']);

        if ($msgId) {
            $query = $query->where('id', '=', $msgId);
        }

        $subscription = Subscription::query()
            ->where('room_id', '=', $attributes['room_id'])
            ->where('user_id', '=', $context->entityId())
            ->first();

        if (!empty($subscription) && $subscription->is_deleted == 0 && !empty($subscription->rejoin_at)) {
            $query = $query->where('created_at', '>=', $subscription->rejoin_at);
        }

        return $query->orderByDesc('created_at')
            ->simplePaginate($limit, ['chat_messages.*']);
    }

    public function getRelatedMessages(Message $message, array $attributes): array
    {
        $topMessages    = [];
        $bottomMessages = [];
        $scroll         = Arr::get($attributes, 'scroll');
        $upperBound     = Arr::get($attributes, 'upper_bound');
        $lowerBound     = Arr::get($attributes, 'lower_bound');

        if (Arr::get($attributes, 'message_id')) {
            if ($scroll == 'up' || $scroll == 'all') {
                $topMessages = $this->getModel()->newQuery()
                    ->where('room_id', '=', $message->room_id)
                    ->where('id', '<', $message->id)
                    ->orderByDesc('created_at')
                    ->limit($upperBound)
                    ->get();
            }

            if ($scroll == 'down' || $scroll == 'all') {
                $bottomMessages = $this->getModel()->newQuery()
                    ->where('room_id', '=', $message->room_id)
                    ->where('id', '>', $message->id)
                    ->orderBy('created_at')
                    ->limit($lowerBound)
                    ->get();
            }
        }

        return [
            'top_messages'    => $topMessages,
            'bottom_messages' => $bottomMessages,
        ];
    }

    public function viewMessage(User $context, int $id): Message
    {
        return $this
            ->with(['attachments'])
            ->find($id);
    }

    public function addMessage(User $context, array $attributes): Message|null
    {
        $room = Room::query()->getModel()
            ->with(['subscriptions'])
            ->find($attributes['room_id']);

        policy_authorize(MessagePolicy::class, 'create', $context, $room);

        $replyId = (int) Arr::get($attributes, 'reply_id', 0);
        $extra   = null;
        if ($replyId) {
            $message       = $this->getModel()->newQuery()->find($replyId);
            $messageDetail =  new ReplyMessageDetail($message);
            $extra         = $messageDetail->toJson();
        }

        $attributes = array_merge([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'extra'     => $extra,
        ], $attributes);

        $message = new Message($attributes);
        $message->save();

        if (!empty($attributes['attachments'])) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attributes['attachments'], $message);
        }

        $message->refresh();

        Subscription::query()->getModel()
            ->where([
                'room_id' => $attributes['room_id'],
            ])
            ->touch('updated_at');

        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($attributes['room_id']);

        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            if ($subscription->is_showed == 0) {
                $subscription->update(['is_showed' => 1]);
            }
            if (($subscription->user_id != $context->entityId())) {
                if ($subscription->is_deleted) {
                    $subscription->update(['is_deleted' => 0, 'rejoin_at' => $message->created_at]);
                }

                $subscription->increment('total_unseen');
                $subscription->update([
                    'is_seen_notification' => 0,
                ]);
                $subscription->refresh();

                //update notification count
                broadcast(new UserNotificationMessage($subscription->user_id, Helper::NOTIFICATION_UPDATE, 'add_message'));
            }
            broadcast(new RoomMessage($attributes['room_id'], $userId, Room::ROOM_UPDATED));
            MessageQueueJob::dispatch($message, $userId, Message::MESSAGE_CREATE, Arr::get($attributes, 'tempId'));
            if ($subscription->user->id !== $context->userId()) {
                Notification::sendNow($subscription->user, new NewMessageNotification($message));
            }
        }

        return $message;
    }

    public function getRoomLastMessage(int $userId, int $roomId): Message|null
    {
        $query = $this->getModel()->newQuery();

        $subscription = Subscription::query()
            ->where('user_id', '=', $userId)
            ->where('room_id', '=', $roomId)
            ->first();

        if (!empty($subscription) && $subscription->is_deleted == 0 && !empty($subscription->rejoin_at)) {
            $query = $query->where('created_at', '>=', $subscription->rejoin_at);
        }

        return $query
            ->with(['attachments'])
            ->where('room_id', '=', $roomId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function updateMessage(User $context, int $id, array $attributes): Message
    {
        $message = $this->find($id);
        $type    = Arr::get($attributes, 'type');

        if ($type === 'delete') {
            policy_authorize(MessagePolicy::class, 'delete', $context, $message);
        } else {
            policy_authorize(MessagePolicy::class, 'update', $context, $message);
        }

        $message->fill($attributes);
        $message->save();
        $message->refresh();

        if ($type === 'delete') {
            Subscription::query()->getModel()
                ->where([
                    'room_id' => $message->room_id,
                ])
                ->touch('updated_at');
            $message->attachments()->delete();
        }

        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($message->room_id);
        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            if ($subscription->is_deleted == 0) {
                MessageQueueJob::dispatch($message, $userId, Message::MESSAGE_UPDATE);
                broadcast(new RoomMessage($message->room_id, $userId, Room::ROOM_UPDATED));
            }
        }

        return $message;
    }

    public function reactMessage(User $context, int $id, array $params): Message
    {
        $message = $this->find($id);

        $reactions = $message->reactions;

        $itemReactions = [];
        if (!empty($reactions)) {
            $itemReactions = json_decode($reactions, true);
        }

        if ($params['remove'] || $params['react'] != '') {
            foreach ($itemReactions as $reactKey => $itemReaction) {
                if (in_array($context->entityId(), $itemReaction)) {
                    $idx = array_search($context->entityId(), $itemReaction);
                    unset($itemReactions[$reactKey][$idx]);
                }
            }
        }

        if ($params['react'] != '') {
            $itemReactions[$params['react']][] = $context->entityId();
        }

        foreach ($itemReactions as $reactKey => $itemReaction) {
            if (count($itemReaction) == 0) {
                unset($itemReactions[$reactKey]);
            }
        }

        $message->fill(['reactions' => count($itemReactions) == 0 ? null : json_encode($itemReactions)]);
        $message->save();
        $message->refresh();

        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($message->room_id);
        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            if ($subscription->is_deleted == 0) {
                MessageQueueJob::dispatch($message, $userId, Message::MESSAGE_UPDATE);
            }
        }

        return $message;
    }

    public function normalizeReactions(array|null $reactions): array
    {
        if (!app_active('metafox/like')) {
            return [];
        }

        if ($reactions == null) {
            return [];
        }

        $reactionsDetails = [];
        foreach ($reactions as $key => $reaction) {
            $reactionKey = str_replace(':', '', $key);
            $reactionId  = explode('_', $reactionKey);
            try {
                $reactionItem = resolve(ReactionRepositoryInterface::class)->find($reactionId[1]);
                if (!empty($reactionItem)) {
                    foreach ($reaction as $item) {
                        $user                     = resolve(UserRepositoryInterface::class)->find($item);
                        $reactionsDetails[$key][] = new UserSimple($user);
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $reactionsDetails;
    }

    public function downloadAttachment(User $context, int $id): Attachment
    {
        return resolve(AttachmentRepositoryInterface::class)->find($id);
    }

    public function deleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void
    {
        DeleteOrMoveReactionJob::dispatch($reactionId, $newReactionId);
    }

    public function performActionDeleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void
    {
        $reactionIdKey = ':reaction_' . $reactionId . ':';
        $messages      = $this->getModel()->newQuery()
            ->where('reactions', $this->likeOperator(), '%":reaction_' . $reactionId . ':"%')
            ->get();

        foreach ($messages as $message) {
            $reactions     = $message->reactions;
            $itemReactions = [];
            if (!empty($reactions)) {
                $itemReactions = json_decode($reactions, true);
            }

            $reactionValues = [];
            if (array_key_exists($reactionIdKey, $itemReactions)) {
                $reactionValues = $itemReactions[$reactionIdKey];
                unset($itemReactions[$reactionIdKey]);
            }
            $itemReactions = array_values($itemReactions);

            if ($newReactionId) {
                $newReactionIdKey = ':reaction_' . $newReactionId . ':';
                if (!array_key_exists($newReactionIdKey, $itemReactions)) {
                    $itemReactions[$newReactionIdKey] = $reactionValues;
                }
            }

            $message->fill(['reactions' => count($itemReactions) == 0 ? null : json_encode($itemReactions)]);
            $message->save();
        }
    }

    protected function userNotificationRepository(): UserNotificationRepositoryInterface
    {
        return resolve(UserNotificationRepositoryInterface::class);
    }
}
