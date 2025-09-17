<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyStreamRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\Membership;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class PrivacyListRepository.
 *
 * @method   Privacy getModel()
 * @property Privacy $model
 * @method   Privacy create(array $attributes)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class PrivacyRepository extends AbstractRepository implements PrivacyRepositoryInterface
{
    public function model()
    {
        return Privacy::class;
    }

    public function getPrivacyId(int $itemId, string $itemType, string $privacyType): int
    {
        /** @var Privacy $privacy */
        $privacy = $this->getModel()->where([
            'item_id'      => $itemId,
            'item_type'    => $itemType,
            'privacy_type' => $privacyType,
        ])->first();

        if (null != $privacy) {
            return $privacy->privacy_id;
        }

        return 0;
    }

    public function getPrivacyIdsForContent(Entity $content): array
    {
        if (!$content instanceof HasPrivacy) {
            return [
                MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
            ];
        }

        $privacyKey = $this->getModel()->getKeyName();

        if (!$content instanceof User) {
            try {
                $owner = UserEntity::getById($content->ownerId())->detail;
            } catch (\Throwable) {
                throw new \RuntimeException(sprintf(
                    'Could not get owner %s:%s of %s:%s',
                    $content->ownerType(),
                    $content->ownerId(),
                    $content->entityType(),
                    $content->entityId()
                ));
            }

            if ($owner->getItemPrivacy() !== null) {
                $content->privacy = $owner->getPrivacyItem();
            }
        }

        switch ($content->privacy) {
            case MetaFoxPrivacy::MEMBERS:
                return [MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID];
            case MetaFoxPrivacy::EVERYONE:
                // Public privacy: 1.
                return [MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID];
            case MetaFoxPrivacy::FRIENDS_OF_FRIENDS:
                return array_merge(
                    [MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID],
                    $this->getPrivacyIdsByPrivacy($content->ownerId(), MetaFoxPrivacy::FRIENDS)
                );
            case MetaFoxPrivacy::FRIENDS:
                return $this->getPrivacyIdsByPrivacy($content->ownerId(), MetaFoxPrivacy::FRIENDS);
            case MetaFoxPrivacy::ONLY_ME:
                return $this->getPrivacyIdsByPrivacy($content->ownerId(), MetaFoxPrivacy::ONLY_ME);
            case MetaFoxPrivacy::CUSTOM:
                if (!empty($content->privacy_list)) {
                    return $this->getModel()
                        ->whereIn('item_id', $content->privacy_list)
                        ->where('owner_id', '=', $content->ownerId())
                        ->where('privacy', '=', MetaFoxPrivacy::CUSTOM)
                        ->get()
                        ->pluck($privacyKey)
                        ->toArray();
                }

                return [];
            default:
                return [];
        }
    }

    public function getPrivacyIdsForMembership(Membership $membership): array
    {
        $privacy = $membership->privacy();

        //Can not use ownerId in some special cases which needs userId instead
        $userId = $membership->privacyUserId();

        if (null !== $privacy && null !== $userId) {
            return [$this->getPrivacyIdForUserPrivacy($userId, $privacy)];
        }

        return [MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID];
    }

    public function getPrivacyIdForUserPrivacy(int $ownerId, int $privacy): int
    {
        switch ($privacy) {
            case MetaFoxPrivacy::MEMBERS:
                return MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID;
            case MetaFoxPrivacy::EVERYONE:
                return MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID;
            case MetaFoxPrivacy::FRIENDS_OF_FRIENDS:
                // @todo NamNV: need to filter friend of friends.
                return 0;
            case MetaFoxPrivacy::FRIENDS:
            case MetaFoxPrivacy::ONLY_ME:
            case MetaFoxPrivacy::CUSTOM:
                return $this->getPrivacyIdByPrivacy($ownerId, $privacy);
            default:
                return 0;
        }
    }

    public function getPrivacyTypeByPrivacy(int $ownerId, int $privacy): ?string
    {
        $collection = $this->getPrivaciesByOwnerId($ownerId);

        if (!$collection->count()) {
            return null;
        }

        $source = $collection->first(function ($item) use ($privacy) {
            return $item->privacy == $privacy;
        });

        if (null === $source) {
            return null;
        }

        return $source->privacy_type;
    }

    protected function getPrivacyIdsByPrivacy(int $ownerId, int $privacy): array
    {
        $collection = $this->getPrivaciesByOwnerId($ownerId);

        if (!$collection->count()) {
            return [];
        }

        $collection = $collection->filter(function ($item) use ($privacy) {
            return $item->privacy == $privacy;
        });

        $primaryKey = $this->getModel()->getKeyName();

        return $collection
            ->pluck($primaryKey)
            ->toArray();
    }

    protected function getPrivacyIdByPrivacy(int $ownerId, int $privacy): int
    {
        $collection = $this->getPrivaciesByOwnerId($ownerId);

        if (!$collection->count()) {
            return 0;
        }

        $privacy = $collection->first(function ($item) use ($privacy) {
            return $item->privacy == $privacy;
        });

        if (null !== $privacy) {
            return $privacy->{$privacy->getKeyName()};
        }

        return 0;
    }

    protected function getPrivaciesByOwnerId(int $ownerId): Collection
    {
        return Cache::rememberForever('core_privacy_owner_' . $ownerId, function () use ($ownerId) {
            return $this->getModel()->newModelQuery()
                ->where('owner_id', '=', $ownerId)
                ->get();
        });
    }

    public function forceCreatePrivacyStream(Entity $model): void
    {
        $privacyUidList = $this->getPrivacyIdsForContent($model);

        if (!count($privacyUidList)) {
            return;
        }

        $privacyStreams = array_map(function ($privacyId) use ($model) {
            return [
                'privacy_id' => $privacyId,
                'item_id'    => $model->entityId(),
                'item_type'  => $model->entityType(),
            ];
        }, $privacyUidList);

        resolve(PrivacyStreamRepositoryInterface::class)->createMany($privacyStreams);

        if (method_exists($model, 'syncPrivacyStreams')) {
            $model->syncPrivacyStreams(array_map(function ($array) {
                unset($array['item_type']);

                return $array;
            }, $privacyStreams));
        }
    }

    public function getCustomPrivacyOptions(User $context, array $params = []): Arrayable
    {
        $lists = app('events')->dispatch('friend.list.get', [$context, $params], true);

        if (null === $lists) {
            return collect([]);
        }

        $itemId = Arr::get($params, 'item_id');

        $itemType = Arr::get($params, 'item_type');

        if (null === $itemType) {
            return $lists;
        }

        if (null === $itemId) {
            return $lists;
        }

        unset($params['item_id']);

        unset($params['item_type']);

        $selectedListIds = $this->getItemCustomPrivacies($itemType, $itemId);

        if (null === $selectedListIds) {
            return $lists;
        }

        return $lists->map(function ($list) use ($selectedListIds) {
            $list->is_selected = in_array($list->entityId(), $selectedListIds);

            return $list;
        });
    }

    protected function getItemCustomPrivacies(string $itemType, int $itemId): ?array
    {
        $morphedModel = Relation::getMorphedModel($itemType);

        if (null === $morphedModel) {
            return null;
        }

        $morphedModel = resolve($morphedModel);

        if (!$morphedModel instanceof HasPrivacy) {
            return null;
        }

        $model = $morphedModel::query()
            ->where([
                $morphedModel->getKeyName() => $itemId,
            ])
            ->first();

        if (null === $model) {
            return null;
        }

        if ($model->privacy !== MetaFoxPrivacy::CUSTOM) {
            return null;
        }

        $selectedLists = PrivacyPolicy::getPrivacyItem($model);

        $selectedListIds = [];

        if (is_array($selectedLists)) {
            $selectedListIds = array_column($selectedLists, 'item_id');
        }

        return $selectedListIds;
    }
}
