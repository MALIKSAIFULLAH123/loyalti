<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use Illuminate\Contracts\Database\Eloquent\Builder;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetBuilderFollowerListener.
 *
 * @ignore
 */
class GetBuilderFollowerListener
{
    public function __construct(protected FollowRepositoryInterface $followRepository) { }

    /**
     * @param User $user
     *
     * @return Builder
     */
    public function handle(User $user): Builder
    {
        return $this->followRepository->getFollowerQuery($user);
    }
}
