<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use MetaFox\Core\Repositories\Contracts\PrivacyMemberRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyStreamRepositoryInterface;

/**
 * Trait PreparePrivacyRepositoryTrait.
 */
trait PreparePrivacyRepositoryTrait
{
    public function privacyRepository(): PrivacyRepositoryInterface
    {
        return resolve(PrivacyRepositoryInterface::class);
    }

    public function privacyMemberRepository(): PrivacyMemberRepositoryInterface
    {
        return resolve(PrivacyMemberRepositoryInterface::class);
    }

    public function privacyStreamRepository(): PrivacyStreamRepositoryInterface
    {
        return resolve(PrivacyStreamRepositoryInterface::class);
    }
}
