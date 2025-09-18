<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use MetaFox\Invite\Models\InviteCode;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetUserInviteCodeListener.
 * @ignore
 */
class GetUserInviteCodeListener
{
    public function __construct(protected InviteCodeRepositoryInterface $inviteCodeRepository)
    {
    }

    /**
     * @param ?User $user
     * @return InviteCode|null
     */
    public function handle(?User $user): ?InviteCode
    {
        if ($user == null) {
            return null;
        }
        return $this->inviteCodeRepository->getUserCode($user);
    }
}
