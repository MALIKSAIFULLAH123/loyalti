<?php

namespace MetaFox\Comment\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentAdminRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasApprove;

/**
 * Class CommentAdminRepository.
 * @method Comment getModel()
 * @method Comment find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD)
 */
class CommentAdminRepository extends AbstractRepository implements CommentAdminRepositoryInterface
{
    use HasApprove;

    public function model(): string
    {
        return Comment::class;
    }

    public function viewPendingComment(array $attributes): Paginator
    {
        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        return $this->getModel()
            ->newQuery()
            ->where('is_approved', '<>', 1)
            ->orderByDesc('id')
            ->paginate($limit);
    }

    public function decline(int $id): bool
    {
        $comment = $this->getModel()
            ->newQuery()
            ->where('id', $id)
            ->where('is_approved', '<>', 1)
            ->firstOrFail();

        if (!$comment->delete()) {
            return false;
        }

        return true;
    }
}
