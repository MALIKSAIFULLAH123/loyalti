<?php

namespace MetaFox\Platform\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Support\Facades\User as UserFacade;

class PreventPendingSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user instanceof User && Auth::id() != MetaFoxConstant::GUEST_USER_ID) {
            $pendingInformation = UserFacade::hasPendingSubscription($request, $user);

            if (is_array($pendingInformation)) {
                // 426 is status code for Pending Subscription
                return response()->json($pendingInformation, 426);
            }
        }

        return $next($request);
    }
}
