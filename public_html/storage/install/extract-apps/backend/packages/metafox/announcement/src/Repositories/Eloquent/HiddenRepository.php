<?php

namespace MetaFox\Announcement\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden;
use MetaFox\Announcement\Policies\AnnouncementPolicy;
use MetaFox\Announcement\Repositories\HiddenRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class HiddenRepository.
 * @property Hidden $model
 * @method   Hidden getModel()
 * @ignore
 * @codeCoverageIgnore
 */
class HiddenRepository extends AbstractRepository implements HiddenRepositoryInterface
{
    public function model(): string
    {
        return Hidden::class;
    }

    /**
     * @param  User                   $context
     * @param  Announcement           $resource
     * @return Hidden
     * @throws AuthorizationException
     */
    public function createHidden(User $context, Announcement $resource): Hidden
    {
        policy_authorize(AnnouncementPolicy::class, 'view', $context, $resource);

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
}
