<?php

namespace MetaFox\Socialite\Http\Controllers\Api\v1;

use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\SocialAccount;
use MetaFox\User\Models\User;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Socialite\Http\Requests\v1\Auth\CallbackRequest;
use MetaFox\Socialite\Http\Requests\v1\Auth\LoginRequest;
use MetaFox\Socialite\Http\Requests\v1\Invite\VerifyInviteRequest;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;

/**
 * Class SocialAccountController.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SocialAccountController extends ApiController
{
    protected const INVITE_REDIRECT_URL = 'invite/verify';

    public function __construct(
        protected SocialAccountRepositoryInterface $socialAccountRepository
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $provider = $request->validated('provider');

        $data = app('events')->dispatch('socialite.social_account.request', $provider);

        return $this->success($data);
    }

    public function callback(CallbackRequest $request, string $provider): JsonResponse
    {
        /** @var null|SocialAccount $socialAccount */
        $socialAccount = app('events')->dispatch('socialite.social_account.callback', [
            $provider, $request->all(),
        ], true);

        if (!$socialAccount instanceof SocialAccount) {
            return $this->error('Something went wrong');
        }

        if ($socialAccount->user) {
            $response = $this->socialAccountRepository->processUserSignIn($socialAccount);

            return $this->success($response);
        }

        if (!$this->socialAccountRepository->isRequiredVerifyInviteCode(false)) {
            return $this->error('Something went wrong');
        }

        $redirectUrl = self::INVITE_REDIRECT_URL . '?' . http_build_query(['hash' => $socialAccount->hash]);

        return $this->success([
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function verifyInvite(VerifyInviteRequest $request): JsonResponse
    {
        $params = $request->validated();

        $socialAccount = $this->socialAccountRepository
            ->findSocialAccountByHash(Arr::get($params, 'hash'));

        $socialAccount->setAttribute('invite_code', Arr::get($params, 'invite_code'));

        $this->socialAccountRepository->assignUserToSocialAccount($socialAccount);

        $response = $this->socialAccountRepository
            ->processUserSignIn($socialAccount);

        return $this->success($response);
    }
}
