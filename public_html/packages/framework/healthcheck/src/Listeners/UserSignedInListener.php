<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\HealthCheck\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\License;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Exceptions\ValidateUserException;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/UserSignedInListener.stub.
 */

/**
 * Class UserSignedInListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class UserSignedInListener
{
    public function handle($user, $params): void
    {
        if (!$user instanceof User) {
            return;
        }

        match ($user->hasAdminRole()) {
            true  => $this->handleAdminLogin($user, $params),
            false => $this->handleUserLogin($user),
        };
    }

    private function handleAdminLogin(User $user, $params): void
    {
        if (License::isActive()) {
            return;
        }

        // allow login to AdminCP
        if (is_array($params) && Arr::get($params, 'resolution') == MetaFoxConstant::RESOLUTION_ADMIN) {
            return;
        }

        $adminUrl = config('app.mfox_admincp_url');
        if (!url_utility()->isAbsoluteUrl($adminUrl)) {
            $adminUrl = url_utility()->makeApiFullUrl($adminUrl);
        }

        throw new ValidateUserException([
            'format'  => 'html',
            'title'   => __p('user::phrase.invalid_login'),
            'message' => __p('user::phrase.invalid_login_admin_description', [
                'error'       => Settings::get('core.license.error'),
                'admincp_url' => $adminUrl,
            ]),
        ]);
    }

    private function handleUserLogin(User $user): void
    {
        if (License::isActive()) {
            return;
        }

        throw new ValidateUserException([
            'format'  => 'html',
            'title'   => __p('user::phrase.invalid_login'),
            'message' => __p('user::phrase.invalid_login_description'),
        ]);
    }
}
