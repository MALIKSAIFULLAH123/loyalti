<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use MetaFox\Forum\Models\ForumThreadLastRead;
use MetaFox\Forum\Repositories\ForumThreadLastReadRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

class ForumThreadLastReadRepository extends AbstractRepository implements ForumThreadLastReadRepositoryInterface
{
    public function model()
    {
        return ForumThreadLastRead::class;
    }

    public function updateLastRead(int $threadId, int $postId, User $context): bool
    {
        $instance = $this->getModel()->newModelInstance();

        $where = [
            'user_id' => $context->entityId(),
            'user_type' => $context->entityType(),
            'thread_id' => $threadId,
        ];

        $model = $instance
            ->where($where)
            ->first();

        if (null === $model) {
            $timestamp = $this->getModel()->freshTimestamp();

            $data = array_merge($where, [
                'post_id' => $postId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $instance->fill($data);

            $instance->save();

            return true;
        }

        if ($model->post_id != $postId) {
            return $model->update(['post_id' => $postId]);
        }

        return false;
    }

    public function hasRead(User $context, int $threadId): bool
    {
        $query = $this->getModel()->newModelQuery();

        $result = $query->where([
            'user_id' => $context->entityId(),
            'user_type' => $context->entityType(),
            'thread_id' => $threadId,
        ])
            ->count();

        return $result > 0;
    }

    public function updateLastView(User $context, int $threadId): bool
    {
        $instance = $this->getModel()->newModelInstance();

        $where = [
            'user_id' => $context->entityId(),
            'user_type' => $context->entityType(),
            'thread_id' => $threadId,
        ];

        $model = $instance
            ->where($where)
            ->first();

        if (null === $model) {
            return false;
        }

        return $model->update(['updated_at' => $this->getModel()->freshTimestamp()]);
    }
}
