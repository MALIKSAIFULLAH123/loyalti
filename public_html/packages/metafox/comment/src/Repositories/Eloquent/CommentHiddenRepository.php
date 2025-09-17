<?php

namespace MetaFox\Comment\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Comment\Models\CommentHide;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentHiddenRepositoryInterface;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Support\Helper;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class CommentRepository.
 * @method CommentHide getModel()
 * @method CommentHide find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD)
 */
class CommentHiddenRepository extends AbstractRepository implements CommentHiddenRepositoryInterface
{
    public function model(): string
    {
        return CommentHide::class;
    }

    protected function commentRepository(): CommentRepositoryInterface
    {
        return resolve(CommentRepositoryInterface::class);
    }

    /**
     * @throws AuthorizationException
     */
    public function hideComment(User $context, int $id, bool $isHidden): bool
    {
        $comment = $this->commentRepository()->getModel()->newQuery()
            ->with(['user', 'owner'])
            ->find($id);

        policy_authorize(CommentPolicy::class, 'hide', $context, $comment);

        return $this->processContextHidden($context, $id, $isHidden, Helper::HIDE_OWN);
    }

    /**
     * @throws AuthorizationException
     */
    public function hideCommentGlobal(User $context, int $id, bool $isHidden): bool
    {
        $comment = $this->commentRepository()->getModel()->newQuery()
            ->with(['user', 'owner'])
            ->find($id);

        policy_authorize(CommentPolicy::class, 'hideGlobal', $context, $comment);

        $owner = $comment->owner;

        $user = $comment->user;

        $isOwner = null !== $owner && $context->entityId() == $owner->entityId();

        // Push view permission for owner of comment
        if (null !== $user) {
            $this->processRelatedHidden($comment->user, $id, $isHidden);
        }

        // Push view permission when context is owner of post
        if ($isOwner) {
            return $this->processContextHidden(
                $owner,
                $id,
                $isHidden,
                $isHidden ? Helper::HIDE_GLOBAL : Helper::HIDE_OWN
            );
        }

        // Push view permission for owner of post
        $this->processRelatedHidden($owner, $id, $isHidden);

        // Push hidden view for context in case context has moderate permission
        return $this->processContextHidden($context, $id, $isHidden, Helper::HIDE_OWN);
    }

    /**
     * In case owner of item and owner of comment.
     * @param  User $user
     * @param  int  $id
     * @param  bool $isHidden
     * @return bool
     */
    protected function processRelatedHidden(User $user, int $id, bool $isHidden): bool
    {
        $model = $this->getModel()->newQuery()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'item_id'   => $id,
            ])
            ->first();

        $type = $isHidden ? Helper::HIDE_GLOBAL : Helper::HIDE_OWN;

        if ($model instanceof CommentHide) {
            if ($model->type != $type) {
                return $model->update(['type' => $type]);
            }

            return true;
        }

        return $this->getModel()->fill([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_id'   => $id,
            'type'      => $type,
            'is_hidden' => false,
        ])->save();
    }

    /**
     * In case context is executed action hide.
     * @param  User   $user
     * @param  int    $id
     * @param  bool   $isHidden
     * @param  string $type
     * @return bool
     */
    protected function processContextHidden(User $user, int $id, bool $isHidden, string $type): bool
    {
        $model = $this->getModel()->newQuery()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'item_id'   => $id,
            ])
            ->first();

        if ($model instanceof CommentHide) {
            $update = ['is_hidden' => $isHidden];

            if ($type != $model->type) {
                Arr::set($update, 'type', $type);
            }

            return $model->update($update);
        }

        return $this->getModel()->fill([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_id'   => $id,
            'type'      => $type,
            'is_hidden' => $isHidden,
        ])->save();
    }
}
