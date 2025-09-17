<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories;

use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Core\Repositories\Contracts\PrivacyMemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class PrivacyMemberRepository.
 */
class PrivacyMemberRepository extends AbstractRepository implements PrivacyMemberRepositoryInterface
{
    public function model()
    {
        return PrivacyMember::class;
    }

    public function getPrivacyIds(User $user): array
    {
        return $this->getModel()->newQuery()
            ->where([
                'user_id' => $user->entityId(),
            ])
            ->get()
            ->pluck('privacy_id')
            ->toArray();
    }
}
