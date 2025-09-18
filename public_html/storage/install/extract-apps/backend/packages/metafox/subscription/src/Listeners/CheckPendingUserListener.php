<?php

namespace MetaFox\Subscription\Listeners;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

class CheckPendingUserListener
{
    public function handle(Request $request, User $user, bool $isMobile = false): ?array
    {
        $apiPrefix = trim(config('app.mfox_api_url', MetaFoxConstant::EMPTY_STRING), '/');

        $segments = $request->segments();

        $parse = implode('/', $segments);

        $parse = trim(Str::replace($apiPrefix, '', $parse), '/');

        $segments = explode('/', $parse);

        if (!count($segments)) {
            return null;
        }

        $full = implode('/', $segments);

        $first = array_shift($segments);

        if (preg_match('/^subscription(-[a-z]+)?$/', $first)) {
            return null;
        }

        $allowedEndpoints = $this->getAllowedEndpointsForPendingSubscription();

        if (in_array($first, $allowedEndpoints)) {
            return null;
        }

        $checkPending = true;

        foreach ($allowedEndpoints as $allowedEndpoint) {
            $pattern = sprintf('%s', $allowedEndpoint);
            Log::channel('dev')->info($pattern);
            if (preg_match('/' . $pattern . '/', $full)) {
                $checkPending = false;
                break;
            }
        }

        if (!$checkPending) {
            return null;
        }

        return app('events')->dispatch('subscription.invoice.has_pending', [$user, $isMobile], true);
    }

    protected function getAllowedEndpointsForPendingSubscription(): array
    {
        $coreAliases = Cache::rememberForever(
            __METHOD__,
            fn () => Package::query()
                ->where([
                    'is_active'    => 1,
                    'is_installed' => 1,
                    'is_core'      => 1,
                ])
                ->pluck('alias')
                ->toArray()
        );

        $coreAliases = array_merge($coreAliases, ['subscription']);

        $allowedEndpoints = [
            'me',
            'auth\/logout',
            'core\/web\/(settings|app-settings|action-settings)',
            'core\/mobile\/(settings|app-settings|action-settings)',
            'core\/admin\/settings',
            'core\/(status|translation|package)',
            'core\/form\/(' . implode('|', $coreAliases) . ')',
            'seo',
            'chat-room',
            'auth\/logout'
        ];

        $extraEndpoints = app('events')->dispatch('user.pending_subscription.allow_endpoints');

        if (!is_array($extraEndpoints)) {
            return $allowedEndpoints;
        }

        foreach ($extraEndpoints as $extraEndpoint) {
            if (!is_array($extraEndpoint)) {
                continue;
            }

            $allowedEndpoints = array_merge($allowedEndpoints, $extraEndpoint);
        }

        return array_unique($allowedEndpoints);
    }
}
