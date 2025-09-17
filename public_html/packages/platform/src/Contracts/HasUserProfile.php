<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\User\Models\UserProfile;

/**
 * Interface HasUserProfile.
 * @property UserProfile $profile
 */
interface HasUserProfile
{
    public function profile(): HasOne;
}
