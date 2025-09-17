<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class UpdateProfileAvatarListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateProfileAvatarListener
{
    public function __construct(protected UserRepositoryInterface $repository) { }

    /**
     * @param User                $context
     * @param User                $owner
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(User $context, User $owner, array $attributes): array
    {
        return $this->repository->updateAvatar($context, $owner, $attributes);
    }
}
