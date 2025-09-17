<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Platform\Contracts\User as UserContract;

/**
 * Class GetBuilderUserRecommendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetBuilderUserRecommendListener
{
    public function __construct(protected FriendRepositoryInterface $friendRepository)
    {
    }

    /**
     * @param  UserContract $context
     * @param  Builder      $builder
     * @return Builder
     */
    public function handle(UserContract $context, Builder $builder): Builder
    {
        return $this->friendRepository->getBuilderUserRecommend($context, $builder);
    }
}
