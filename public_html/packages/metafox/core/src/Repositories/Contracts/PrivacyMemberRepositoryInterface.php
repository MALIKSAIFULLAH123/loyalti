<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories\Contracts;

use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface PrivacyDataRepositoryInterface.
 * @mixin RepositoryInterface
 * @method               create(array $attributes)
 * @method PrivacyMember getModel()
 */
interface PrivacyMemberRepositoryInterface
{
    /**
     * Get all privacy ids of user.
     *
     * @param User $user
     *
     * @return int[]
     */
    public function getPrivacyIds(User $user): array;
}
