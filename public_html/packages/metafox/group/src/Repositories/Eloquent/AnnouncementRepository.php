<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Announcement;
use MetaFox\Group\Models\AnnouncementHide;
use MetaFox\Group\Policies\AnnouncementPolicy;
use MetaFox\Group\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AnnouncementRepository.
 *
 * @method Announcement getModel()
 * @method Announcement find($id, $columns = ['*'])
 */
class AnnouncementRepository extends AbstractRepository implements AnnouncementRepositoryInterface
{
    public function model()
    {
        return Announcement::class;
    }

    protected function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewAnnouncements(User $context, array $attributes): Paginator
    {
        $limit   = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $groupId = Arr::get($attributes, 'group_id');

        $query = $this->getModel()->newQuery();

        return $query
            ->where('group_id', $groupId)
            ->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function createAnnouncement(User $context, array $attributes): Announcement
    {
        $data = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];
        $attributes = array_merge($attributes, $data);

        $announcement = $this->getModel()->newInstance();
        $announcement->fill($attributes);
        $announcement->save();

        return $announcement->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deleteAnnouncement(User $context, array $attributes)
    {
        $announcement = $this->getModel()->newInstance()->where($attributes)->first();
        $item         = $announcement->item;

        if (!$announcement->delete()) {
            throw new AuthorizationException(null, 403);
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function hideAnnouncement(User $context, array $attributes): bool
    {
        $annId            = Arr::get($attributes, 'ann_id');
        $groupId          = Arr::get($attributes, 'group_id');

        $hidden = AnnouncementHide::query()->where('announcement_id', $annId)->where('group_id', $groupId)->first();

        if ($hidden instanceof AnnouncementHide) {
            return true;
        }

        $hideAnnouncement = new AnnouncementHide();
        $hideAnnouncement->fill([
            'announcement_id' => $annId,
            'group_id'        => $groupId,
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
        ])->save();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function checkExistsAnnouncement(int $groupId, int $itemId, string $itemType): bool
    {
        $announcement = $this->getModel()->newInstance();

        return $announcement->where([
            'group_id'  => $groupId,
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ])->exists();
    }

    /**
     * @inheritDoc
     */
    public function deleteByItem(int $itemId, string $itemType): void
    {
        $announcement = $this->getModel()->newInstance();

        $announcement->where([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ])?->delete();
    }
    public function getTotalUnread(User $context, int $groupId): int
    {
        $query = $this->getModel()->newQuery()
            ->select('announce.*')
            ->from('group_announcements as announce');

        $query->leftJoin('group_announcement_hidden as hide', function (JoinClause $join) use ($context) {
            $join->on('announce.id', '=', 'hide.announcement_id')
                ->where('hide.user_id', '=', $context->entityId());
        });

        $query->where(function (Builder $whereQuery) {
            $whereQuery->whereNull('hide.id');
        });

        return $query
            ->where('announce.group_id', $groupId)->count();
    }
}
