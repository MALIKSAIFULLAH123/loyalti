<?php

namespace MetaFox\Announcement\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Announcement\Models\AnnouncementView as Model;
use MetaFox\Announcement\Policies\AnnouncementPolicy;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class AnnouncementViewRepository.
 *
 * @method Model getModel()
 * @method Model find($id, $column = ['*'])
 */
class AnnouncementViewRepository extends AbstractRepository implements AnnouncementViewRepositoryInterface
{
    use UserMorphTrait;

    public const VIEWED_IDS_CACHE_ID = 'viewed_announcement_ids_user_%s';

    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function viewAnnouncementViews(User $context, array $params): Paginator
    {
        $limit = Arr::get($params, 'limit');
        $id    = Arr::get($params, 'announcement_id', 0);

        return $this->getModel()
            ->newModelQuery()
            ->with(['user', 'userEntity'])
            ->whereHas('user')
            ->where('announcement_id', '=', $id)
            ->simplePaginate($limit);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function createAnnouncementView(User $context, array $params): Model
    {
        $id           = Arr::get($params, 'announcement_id', 0);

        $announcement = $this->announcementRepository()
            ->with(['announcementText', 'style', 'views'])
            ->find($id);

        policy_authorize(AnnouncementPolicy::class, 'markAsRead', $context, $announcement);

        $this->clearCache($context);

        return $this->getModel()->newModelQuery()->firstOrCreate([
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'announcement_id' => $id,
        ]);
    }

    protected function announcementRepository(): AnnouncementRepositoryInterface
    {
        return resolve(AnnouncementRepositoryInterface::class);
    }

    public function checkViewAnnouncement(int $userId, int $announcementId): bool
    {
        $viewedIds = $this->getViewedIdsByUserId($userId);

        return in_array($announcementId, $viewedIds);
    }

    public function getViewedIdsByUserId(int $userId): array
    {
        return localCacheStore()->rememberForever(sprintf(self::VIEWED_IDS_CACHE_ID, $userId), function () use ($userId) {
            return $this->getModel()->newQuery()
                ->where([
                    'user_id' => $userId,
                ])
                ->pluck('announcement_id')
                ->toArray();
        });
    }

    protected function clearCache(User $user): void
    {
        localCacheStore()->forget(sprintf(self::VIEWED_IDS_CACHE_ID, $user->entityId()));
    }
}
