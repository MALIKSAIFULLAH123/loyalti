<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;

class ParseRedirectRouteListener
{
    public function handle(...$params): ?array
    {
        [$path] = $params;

        if (!in_array($path, ['user-profile'])) {
            return null;
        }

        $contextUser = Auth::user();
        if (!$contextUser instanceof User || $contextUser->hasRole([UserRole::GUEST_USER])) {
            return null;
        }

        return [
            'path' => $contextUser->toLink(),
        ];
    }
}
