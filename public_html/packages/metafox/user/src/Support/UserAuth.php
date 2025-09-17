<?php

namespace MetaFox\User\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestFacades;
use Illuminate\Support\Facades\Route;
use MetaFox\Mfa\Support\Facades\Mfa as MfaFacade;
use MetaFox\Mfa\Support\Mfa;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Contracts\UserAuth as ContractsUserAuth;
use MetaFox\User\Exceptions\ValidateUserException;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Security\BruteForceLoginProtection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UserAuth implements ContractsUserAuth
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function login(FormRequest $request)
    {
        $params     = $request->validated();
        $username   = $request->validated('username', '');
        $password   = $request->validated('password', '');
        $resolution = Arr::get($request->all(), 'resolution', MetaFoxConstant::RESOLUTION_WEB);

        $user = $this->userRepository->findAndValidateForAuth($username, $password);

        $this->validateMobilePendingSubscriptionUser($user);

        $response = app('events')->dispatch('user.request_mfa_token', [$user, $resolution], true);

        if ($response) {
            /*
             * @deprecated Remove in 5.2
             */
            if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
                return $response;
            }

            return [
                'redirect_url' => Mfa::MFA_REDIRECT_URL . '?' . http_build_query(array_merge($response, Arr::only($params, MfaFacade::getAuthQueryParamsAttribute()))),
            ];
        }

        app('events')->dispatch('user.signing_in', [$user, $params]);

        $response = $this->authorize($request);

        app('events')->dispatch('user.signed_in', [$user, $params]);

        return $response;
    }

    protected function validateMobilePendingSubscriptionUser(?UserModel $user): void
    {
        if (!$user instanceof UserModel) {
            return;
        }

        if (!MetaFox::isMobile()) {
            return;
        }

        $pendingUser = app('events')->dispatch('subscription.pending_user.login', [$user], true);

        if (null === $pendingUser) {
            return;
        }

        $isPending = Arr::get($pendingUser, 'is_pending', false);

        if (!$isPending) {
            return;
        }

        abort(403, json_encode(Arr::get($pendingUser, 'error_message', [])));
    }

    public function fixApiSecret()
    {
        $apiKey    = config('app.api_key');
        $apiSecret = config('app.api_secret');
        $secret    = DB::table('oauth_clients')->where('id', $apiKey)->value('secret');

        if ($secret === $apiSecret) {
            return;
        }

        DB::table('oauth_clients')->where('id', $apiKey)->update(['secret' => $apiSecret]);
    }

    /**
     * authorize.
     *
     * @param FormRequest $request
     *
     * @return mixed
     * @throws ValidateUserException
     */
    public function authorize(FormRequest $request)
    {
        $request->merge([
            'client_id'     => config('app.api_key'),
            'client_secret' => config('app.api_secret'),
            'grant_type'    => 'password',
            'scope'         => '*',
        ]);

        $address         = RequestFacades::ip();
        $bruteForceLogin = new BruteForceLoginProtection();
        $bruteForceLogin->verify(['address' => $address]);

        $proxy    = Request::create('oauth/token', 'POST', $request->validated());
        $response = Route::dispatch($proxy);

        if (!$response->isOk()) {
            $bruteForceLogin->process(['address' => $address]);
            $bruteForceLogin->verify(['address' => $address]);

            $content = json_decode($response->getContent(), true);
            if ($error = json_decode(Arr::get($content, 'error'), true)) {
                // handle custom error'
                $content = $error;
            }

            $params = [
                'title'   => __p('user::phrase.oops_login_failed'),
                'message' => __p('user::phrase.the_user_credentials_were_incorrect'),
            ];

            if (is_array($content)) {
                $params = array_merge($content, $params);
            }

            abort(403, json_encode($params));
        }

        $bruteForceLogin->clearCache($address);

        return $response;
    }
}
