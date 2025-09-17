<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Support\Facades\User as UserFacade;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param Closure  $next
     * @param string[] ...$guards
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        if (Auth::guest()) {
            if ($request->getMethod() === RequestAlias::METHOD_GET) {
                $guestUser = UserFacade::getGuestUser();

                Auth::setUser($guestUser);
            }
        }

        $user = Auth::user();

        if ($user instanceof User && Auth::id() != MetaFoxConstant::GUEST_USER_ID) {
            if (!$user->isApproved()) {
                abort(
                    404,
                    __p('user::phrase.your_account_is_now_waiting_for_approval')
                );
            }

            if (method_exists($user, 'hasVerified') && !call_user_func([$user, 'hasVerified'])) {
                abort(404, __p('user::phrase.pending_account_verification'));
            }

            UserFacade::updateLastActivity($user);
        }

        return $next($request);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param Request      $request
     * @param array<mixed> $guards
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function unauthenticated($request, array $guards)
    {
        // No need to throw any.
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    protected function redirectTo($request)
    {
        $request->headers->set('Accept', 'application/json');
        abort(403);
    }
}
