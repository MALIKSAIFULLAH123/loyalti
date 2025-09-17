<?php

namespace MetaFox\Forum\Support;

use MetaFox\Forum\Contracts\ForumThreadSupportContract;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumPostPolicy;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;

class ForumThreadSupport implements ForumThreadSupportContract
{
    public const CAN_SUBSCRIBE   = 'can_subscribe';
    public const CAN_STICK       = 'can_stick';
    public const CAN_CLOSE       = 'can_close';
    public const CAN_MOVE        = 'can_move';
    public const CAN_COPY        = 'can_copy';
    public const CAN_MERGE       = 'can_merge';
    public const CAN_ATTACH_POLL = 'can_attach_poll';
    public const CAN_REPLY       = 'can_reply';

    public const PREFIX_COPY = 'Copy - ';

    protected $repository;

    public function __construct(ForumThreadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCustomPolicies(User $user, Content $resource): array
    {
        $policy = PolicyGate::getPolicyFor(ForumThread::class);

        $owner = $resource->owner;

        return [
            self::CAN_SUBSCRIBE   => $policy->subscribe($user, $resource),
            self::CAN_STICK       => $policy->stick($user, $resource),
            self::CAN_CLOSE       => $policy->close($user, $resource),
            self::CAN_MOVE        => $policy->move($user, $resource),
            self::CAN_COPY        => $policy->copy($user, $owner, $resource),
            self::CAN_MERGE       => $policy->merge($user, $resource),
            self::CAN_ATTACH_POLL => $policy->attachPoll($user, $resource),
            self::CAN_REPLY       => policy_check(ForumPostPolicy::class, 'reply', $user, $resource),
        ];
    }

    public function canDisplayOnWiki(User $user): bool
    {
        return $user->hasPermissionTo('forum_thread.create_as_wiki');
    }

    public function getRelations(): array
    {
        $relations = ['tagData', 'subscribed', 'forum', 'hasRead', 'lastListingPost'];

        return $relations;
    }

    public function getIntegratedItem(User $user, User $owner, ?Entity $entity = null, string $resolution = 'web'): ?array
    {
        $item = app('events')->dispatch('forum.thread.integrated_item.initialize', [$user, $owner, $entity, $resolution], true);

        return $item;
    }

    public function getDefaultMinimumTitleLength(): int
    {
        return MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH;
    }

    public function getDefaultMaximumTitleLength(): int
    {
        return MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
    }

    public function getThread(int $id): ?ForumThread
    {
        return $this->repository->find($id);
    }

    public function checkCreateThreads(User $user): bool
    {
        if (!self::canDisplayOnWiki($user) && !$user->hasPermissionTo('forum_thread.create')) {
            return false;
        }
        
        return true;
    }
}
