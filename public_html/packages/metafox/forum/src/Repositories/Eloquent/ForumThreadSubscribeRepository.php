<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Support\Collection;
use MetaFox\Forum\Models\ForumThreadSubscribe;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadSubscribeRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

class ForumThreadSubscribeRepository extends AbstractRepository implements ForumThreadSubscribeRepositoryInterface
{
    public function model()
    {
        return ForumThreadSubscribe::class;
    }

    public function subscribe(User $context, int $threadId): bool
    {
        $subscribe = $this->getSubscribed($context, $threadId);

        if ($subscribe !== null) {
            return false;
        }

        $subscribe = new ForumThreadSubscribe();

        $subscribe->fill([
            'item_id'   => $threadId,
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        $subscribe->save();

        return true;
    }

    public function unsubscribe(User $context, int $threadId): bool
    {
        $subscribe = $this->getSubscribed($context, $threadId);

        if ($subscribe === null) {
            return false;
        }

        $subscribe->delete();

        return true;
    }

    public function getSubscribed(User $context, int $threadId): ?ForumThreadSubscribe
    {
        $subscribeModel = $this->getModel()->newModelInstance();

        $subscribe = $subscribeModel
            ->where([
                'item_id'   => $threadId,
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ])
            ->first();

        return $subscribe;
    }

    public function getSubscribersOfThreads(array $threadIds = []): ?Collection
    {
        if (!count($threadIds)) {
            return null;
        }

        return $this->getModel()->newModelQuery()
            ->whereIn('item_id', $threadIds)
            ->distinct('user_id', 'user_type')
            ->get();
    }
}
