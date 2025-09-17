<?php

namespace MetaFox\Saved\Policies;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;

/**
 * Class SavedPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class SavedPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Saved::class;

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$resource instanceof Saved) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user, ?User $owner = null, ?string $entityType = null, ?int $entityId = null, bool $inFeed = false): bool
    {
        if (!$user->hasPermissionTo('saved.create')) {
            return false;
        }

        if (null === $entityType) {
            return true;
        }

        $can = $user->hasPermissionTo($entityType . '.save');

        if (null === $entityId) {
            return $can;
        }

        try {
            /**
             * @var Model $class
             */
            [, $class] = resolve(DriverRepositoryInterface::class)->loadDriver(Constants::DRIVER_TYPE_ENTITY, $entityType);

            if (!class_exists($class)) {
                return $can;
            }

            $modelClass = new $class();

            $model = $modelClass->newQuery()
                ->where($modelClass->getKeyName(), $entityId)
                ->first();

            if (!$model instanceof Model) {
                return $can;
            }

            if ($model->force_save_item) {
                return true;
            }

            if ($inFeed) {
                $feed = $model->activity_feed;

                if (!$feed instanceof Model) {
                    return false;
                }

                if ($feed->from_resource === 'feed') {
                    return $user->can('saveItem', [$feed, $feed]);
                }
            }

            $policy = PolicyGate::getPolicyFor($class);

            if (is_object($policy) && method_exists($policy, 'saveItem')) {
                return $policy->saveItem($user, $model);
            }
        } catch (\Throwable $throwable) {
        }

        return $can;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$this->deleteOwn($user, $resource)) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$this->deleteOwn($user, $resource)) {
            return false;
        }

        return true;
    }

    /**
     * @inerhitDoc
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Saved) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function removeItemFromCollection(User $user, ?Content $savedList = null, ?Entity $saved = null): bool
    {
        if (!$savedList instanceof SavedList) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() == $savedList->userId()) {
            return true;
        }

        if (!$saved instanceof Saved) {
            return false;
        }

        if ($user->entityId() != $saved->userId()) {
            return false;
        }

        return true;
    }

    public function addItemToCollection(User $user, ?Entity $saved = null): bool
    {
        if (!$user->hasPermissionTo('saved_list.update')) {
            return false;
        }

        if (!$saved instanceof Saved) {
            return false;
        }

        return $user->entityId() == $saved->userId();
    }
}
