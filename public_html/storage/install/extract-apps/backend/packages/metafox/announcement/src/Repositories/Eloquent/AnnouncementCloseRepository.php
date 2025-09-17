<?php

namespace MetaFox\Announcement\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Policies\AnnouncementPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Announcement\Repositories\AnnouncementCloseRepositoryInterface;
use MetaFox\Announcement\Models\AnnouncementClose;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AnnouncementCloseRepository.
 *
 * @property AnnouncementClose $model
 * @method   AnnouncementClose getModel()
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementCloseRepository extends AbstractRepository implements AnnouncementCloseRepositoryInterface
{
    public function model(): string
    {
        return AnnouncementClose::class;
    }

    /**
     * @param  User                   $context
     * @param  Announcement           $resource
     * @return AnnouncementClose
     * @throws AuthorizationException
     */
    public function closeAnnouncement(User $context, Announcement $resource): AnnouncementClose
    {
        policy_authorize(AnnouncementPolicy::class, 'close', $context, $resource);

        $model = $this->getModel();
        $model->fill([
            'announcement_id' => $resource->entityId(),
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
        ]);
        $model->save();
        $model->refresh();

        return $model;
    }

    public function getCloseAnnouncements(User $context, $announcementId = null): Builder
    {
        $query = $this->getModel()->newQuery()->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType());

        if ($announcementId) {
            $query->where('announcement_id', $announcementId);
        }

        return $query;
    }
}
