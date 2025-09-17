<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Jobs\InviteUserJob;
use MetaFox\Marketplace\Models\Invite;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Policies\InvitePolicy;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;

/**
 * Class InviteRepository.
 * @property Invite $model
 * @method   Invite getModel()
 * @method   Invite find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class InviteRepository extends AbstractRepository implements InviteRepositoryInterface
{
    public function model(): string
    {
        return Invite::class;
    }

    public function inviteFriendsToListing(int $userId, int $listingId, array $userIds): void
    {
        /**
         * @var UserModel $user
         */
        $user = UserModel::query()
            ->where('id', '=', $userId)
            ->first();

        /**
         * @var HasPrivacy|Listing $listing
         */
        $listing = Listing::query()
            ->where('id', '=', $listingId)
            ->first();

        if (null === $user) {
            return;
        }

        if (null === $listing) {
            return;
        }

        if (null === $listing->user) {
            return;
        }

        if (null === $listing->owner) {
            return;
        }

        $ownerEntities = UserEntity::query()
            ->with(['detail'])
            ->whereIn('id', $userIds)
            ->get();

        $privacyIds = [];

        if ($listing->privacy == MetaFoxPrivacy::CUSTOM) {
            $privacyIds = app('events')->dispatch('core.get_privacy_id', [$listing->entityId(), $listing->entityType()], true);
        }

        foreach ($ownerEntities as $ownerEntity) {
            $owner = $ownerEntity->detail;

            if (null === $owner) {
                continue;
            }

            if (!$this->checkInvitePermission($owner, $user, $listing, $privacyIds)) {
                continue;
            }

            Invite::query()
                ->firstOrCreate([
                    'owner_id'   => $owner->entityId(),
                    'owner_type' => $owner->entityType(),
                    'listing_id' => $listing->entityId(),
                ], [
                    'user_id'      => $user->entityId(),
                    'user_type'    => $user->entityType(),
                    'method_type'  => ListingFacade::getInviteUserType(),
                    'method_value' => $owner->entityId(),
                ]);
        }
    }

    protected function checkInvitePermission(User $user, User $context, Listing $listing, ?array $privacyIds = null): bool
    {
        $owner               = $listing->owner;
        $isFriendWithContext = app('events')->dispatch('friend.is_friend', [$user->entityId(), $context->entityId()], true);

        if ($owner instanceof HasPrivacyMember) {
            $privacyItem = method_exists($owner, 'getPrivacyItem') && call_user_func([$owner, 'getPrivacyItem']);

            if ($privacyItem != MetaFoxPrivacy::EVERYONE) {
                return $owner->isMember($user);
            }

            return $owner->isMember($user) || $isFriendWithContext;
        }

        return match ($listing->privacy) {
            MetaFoxPrivacy::EVERYONE,
            MetaFoxPrivacy::MEMBERS            => $isFriendWithContext,
            MetaFoxPrivacy::FRIENDS            => app('events')->dispatch('friend.is_friend', [$user->entityId(), $listing->userId()], true),
            MetaFoxPrivacy::FRIENDS_OF_FRIENDS => app('events')->dispatch('friend.is_friend_of_friend', [$user->entityId(), $listing->userId()], true),
            MetaFoxPrivacy::CUSTOM             => PrivacyPolicy::checkItemPrivacy($user, $listing->user, $listing, $privacyIds),
            default                            => false,
        };
    }

    public function inviteToListing(User $context, array $attributes): bool
    {
        $listingId = (int) $attributes['listing_id'];

        $listing = resolve(ListingRepositoryInterface::class)->find($listingId);

        policy_authorize(ListingPolicy::class, 'invite', $context, $listing);

        $userIds = Arr::get($attributes, 'user_ids', []);

        if (count($userIds)) {
            InviteUserJob::dispatch($context->entityId(), $listing->entityId(), $userIds);
        }

        return true;
    }

    public function getInvitedUserIds(int $listingId): array
    {
        return $this->getModel()->newModelQuery()
            ->where([
                'listing_id' => $listingId,
            ])
            ->get()
            ->pluck('owner_id')
            ->toArray();
    }

    public function visitedAt(User $context, Listing $listing): void
    {
        if (!policy_check(InvitePolicy::class, 'visit', $context, $listing)) {
            return;
        }

        $invite = $this->getInvite($context, $listing->entityId());

        if (null === $invite) {
            return;
        }

        $invite->fill([
            'visited_at' => $invite->freshTimestamp(),
        ]);

        $invite->saveQuietly();
    }

    public function getInvite(User $context, int $listingId): ?Invite
    {
        return $this->getModel()->newModelQuery()
            ->where([
                'owner_id'   => $context->entityId(),
                'owner_type' => $context->entityType(),
                'listing_id' => $listingId,
            ])
            ->first();
    }

    public function viewInvitedPeople(User $context, array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $id = Arr::get($attributes, 'listing_id', 0);

        $listing = resolve(ListingRepositoryInterface::class)->find($id);

        policy_authorize(ListingPolicy::class, 'invite', $context, $listing);

        $query = UserEntity::query()
            ->join('marketplace_invites', function (JoinClause $joinClause) use ($id) {
                $joinClause->on('user_entities.id', '=', 'marketplace_invites.owner_id')
                    ->where('marketplace_invites.listing_id', '=', $id);
            });

        $blockedScope = new BlockedScope();

        $blockedScope->setContextId($context->entityId())
            ->setPrimaryKey('owner_id')
            ->setTable('marketplace_invites');

        return $query->addScope($blockedScope)
            ->orderBy('user_entities.name')
            ->orderByDesc('user_entities.id')
            ->simplePaginate($limit, ['user_entities.*']);
    }
}
