<?php

namespace MetaFox\Mfa\Support\Services;

use Illuminate\Support\Arr;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Support\AbstractService;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use PragmaRX\Google2FALaravel\Google2FA;

/**
 * Class Authenticator.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Authenticator extends AbstractService
{
    private function provider(): Google2FA
    {
        return resolve('pragmarx.google2fa');
    }

    public function setup(User $user, string $service): array
    {
        $siteName = Settings::get('core.general.site_name');
        $holder   = $user->user_name ?? $user->email ?? '';
        $secret   = $this->provider()->generateSecretKey(32);

        $qrCode = $this->provider()->getQRCodeUrl($siteName, $holder, $secret);

        return [
            'value' => $secret,
            'extra' => [
                'qr_code' => $qrCode,
            ],
        ];
    }

    public function toIcon(string $resolution = 'web'): string
    {
        $icon = parent::toIcon($resolution);
        if (!empty($icon)) {
            return $icon;
        }

        return match ($resolution) {
            MetaFoxConstant::RESOLUTION_MOBILE => 'key',
            default                            => 'ico-key',
        };
    }

    public function verifyAuth(UserService $userService, array $params = []): bool
    {
        return $this->verifyCode($userService, Arr::get($params, 'verification_code', ''));
    }

    public function verifyActivation(UserService $userService, array $params = []): bool
    {
        return $this->verifyCode($userService, Arr::get($params, 'verification_code', ''));
    }

    private function verifyCode(UserService $userService, string $code): bool
    {
        $secret = $userService->value;
        if (empty($secret) || empty($code)) {
            return false;
        }

        // TODO: should only verified once
        // if ($userService->last_authenticated) {
        //     $lastTimestamp = Carbon::create($userService->last_authenticated)->getTimestamp();
        // }

        return (bool) $this->provider()->verifyKey($secret, $code, 0);
    }
}
