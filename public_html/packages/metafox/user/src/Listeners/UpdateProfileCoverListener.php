<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class UpdateProfileCoverListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateProfileCoverListener
{
    public function __construct(protected UserRepositoryInterface $repository) { }

    /**
     * @param User                $context
     * @param User                $owner
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     * @throws AuthorizationException
     */
    public function handle(User $context, User $owner, array $attributes): array
    {
        return $this->repository->updateCover($context, $owner, $attributes);
    }
}
