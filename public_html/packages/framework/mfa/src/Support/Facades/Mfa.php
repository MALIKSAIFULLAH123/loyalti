<?php

namespace MetaFox\Mfa\Support\Facades;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Facade;
use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Contracts\ServiceInterface;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Support\Mfa as SupportMfa;
use MetaFox\Platform\Contracts\User;

/**
 * Class Mfa.
 * @method static ServiceInterface service(string $name)
 * @method static array<mixed>     getAllowedServices()
 * @method static array<mixed>     getAllowedAction()
 * @method static UserService initSetup(User $user, string $service): UserService
 * @method static AbstractForm loadSetupForm(UserService $userService, string $resolution = 'web')
 * @method static AbstractForm loadPasswordForm(UserService $userService, string $resolution = 'web')
 * @method static AbstractForm loadAuthForm(string $mfaToken, string $service, string $resolution = 'web')
 * @method static AbstractForm loadServiceSelectionForm(string $mfaToken, string $resolution = 'web')
 * @method static UserService  activate(User $user, UserService $userService, array $params = [])
 * @method static void         deactivate(User $user, string $service)
 * @method static mixed        authenticate(FormRequest $request)
 * @method static bool         resendVerificationAuth(FormRequest $request)
 * @method static bool         resendVerificationSetup(User $user, FormRequest $request)
 * @method static bool         hasMfaEnabled(User $user)
 * @method static bool         hasConfirmPassword(User $user)
 * @method static bool         hasMfaServiceEnabled(User $user, string $service)
 * @method static bool         isAuthenticated(User $user, string $mfaToken)
 * @method static string       requestMfaToken(User $user, string $resolution = 'web')
 * @method static array        getAuthQueryParamsAttribute()
 *
 * @see \MetaFox\Mfa\Support\Mfa
 */
class Mfa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportMfa::class;
    }
}
