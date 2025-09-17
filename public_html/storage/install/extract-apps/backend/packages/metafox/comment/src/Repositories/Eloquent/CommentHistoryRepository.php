<?php

namespace MetaFox\Comment\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentHistoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class CommentHistoryRepository.
 *
 * @method CommentHistory getModel()
 */
class CommentHistoryRepository extends AbstractRepository implements CommentHistoryRepositoryInterface
{
    public function model()
    {
        return CommentHistory::class;
    }

    /**
     * @param  User        $context
     * @param  Comment     $comment
     * @param  string|null $phrase
     * @return void
     */
    public function createHistory(User $context, Comment $comment, string $phrase = null): void
    {
        $data = [
            'comment_id'      => $comment->entityId(),
            'user_id'         => $context->userId(),
            'user_type'       => $context->userType(),
            'content'         => $comment->text_parsed,
            'tagged_user_ids' => $comment->tagged_user_ids,
            'created_at'      => $comment->created_at,
            'phrase'          => $phrase,
        ];

        $history = $this->checkExists($comment);

        if ($history) {
            $data['created_at'] = $comment->updated_at;
            $commentHistory     = $this->getModel()->newQuery()
                ->where('comment_id', $comment->entityId())->get()->last();

            if ($commentHistory->phrase == $phrase) {
                if ($phrase == CommentHistory::PHRASE_ADDED_PHOTO) {
                    $data['phrase'] = CommentHistory::COMMENT_UPDATED_PHOTO;
                }
                if ($phrase == CommentHistory::PHRASE_ADDED_STICKER) {
                    $data['phrase'] = CommentHistory::PHRASE_UPDATED_STICKER;
                }
                if ($phrase == CommentHistory::PHRASE_ADDED_GIF) {
                    $data['phrase'] = CommentHistory::PHRASE_UPDATED_GIF;
                }
            }

            $comment->updateQuietly(['is_edited' => 1]);
        }

        if (!empty($comment->commentAttachment)) {
            $item_id   = $comment->commentAttachment->item_id;
            $item_type = $comment->commentAttachment->item_type;

            if ($item_type == 'gif') {
                $item_id   = 0;
                $item_type = 'storage_file';
            }

            $data = array_merge($data, [
                'item_id'   => $item_id,
                'item_type' => $item_type,
                'params'    => $comment->commentAttachment->params,
            ]);
        }

        $this->getModel()->newQuery()->create($data);
    }

    /**
     * @param  Comment                 $comment
     * @return Collection
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function viewHistory(Comment $comment): Collection
    {
        $context = user();
        policy_authorize(CommentPolicy::class, 'view', $context, $comment);

        return $this->getModel()->newQuery()
            ->where('comment_id', $comment->entityId())->get();
    }

    public function checkExists(Comment $comment): bool
    {
        return $this->getModel()->newQuery()
                ->where('comment_id', $comment->entityId())->count() >= 1;
    }
}
