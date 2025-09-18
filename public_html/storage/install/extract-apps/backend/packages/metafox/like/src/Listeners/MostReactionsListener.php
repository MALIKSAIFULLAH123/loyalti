<?php

namespace MetaFox\Like\Listeners;

use MetaFox\Like\Http\Resources\v1\Reaction\ReactionItemCollection;
use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Class MostReactionsListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class MostReactionsListener
{
    /** @var LikeRepositoryInterface */
    private $repository;

    public function __construct(LikeRepositoryInterface $likeRepository)
    {
        $this->repository = $likeRepository;
    }

    /**
     * @param User         $context
     * @param HasTotalLike $content
     *
     * @return mixed
     */
    public function handle(User $context, HasTotalLike $content)
    {
        if (!policy_check(LikePolicy::class, 'viewAny', $context)) {
            return new ReactionItemCollection([]);
        }

        $reactions = LoadReduce::get(
            sprintf('like::mostReactions(user:%s,%s:%s)', $context?->id, $content->entityType(), $content->entityId()),
            fn () => $this->repository->getMostReactions($context, $content)
        );

        return new ReactionItemCollection($reactions);
    }
}
