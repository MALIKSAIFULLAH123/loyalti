<?php

namespace MetaFox\Socialite\Listeners;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use MetaFox\Platform\MetaFox;
use MetaFox\Socialite\Support\Traits\SocialiteConfigTrait;
use MetaFox\User\Models\SocialAccount;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class SocialAccountCallbackListener.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class SocialAccountCallbackListener
{
    use SocialiteConfigTrait;

    public function __construct(
        protected SocialAccountRepositoryInterface $socialAccountRepository
    ) {}

    /**
     * @param string       $providerName
     * @param array<mixed> $params
     * @return SocialAccount|null
     * @throws ValidatorException
     */
    public function handle(string $providerName, array $params = []): ?SocialAccount
    {
        $socialUser = $this->handleCallbackParameters($providerName, $params);

        if (!$socialUser instanceof SocialUser) {
            abort(400, __p('socialite::validation.something_went_wrong_please_try_later'));
        }

        /** @var ?SocialAccount $socialAccount */
        $socialAccount = $this->socialAccountRepository->findOrCreateSocialAccount($socialUser, $providerName, $params);

        app('events')->dispatch('socialite.social_account', [$socialAccount, $socialUser, $params]);

        /*
         * @deprecated Remove in 5.2
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
            if (!$socialAccount->user instanceof User) {
                $this->socialAccountRepository->assignUserToSocialAccount($socialAccount);
            }

            return $socialAccount;
        }

        if ($this->socialAccountRepository->isRequiredVerifyInviteCode(true, $socialUser->getEmail() ?? '')) {
            return $socialAccount;
        }

        if (!$socialAccount->user instanceof User) {
            $this->socialAccountRepository->assignUserToSocialAccount($socialAccount);
        }

        return $socialAccount;
    }

    /**
     * process the callback parameters and return the socialite user.
     * @param string       $providerName
     * @param array<mixed> $params
     * @return SocialUser|null
     */
    private function handleCallbackParameters(string $providerName, array $params = []): ?SocialUser
    {
        $this->configProvider($providerName);

        // @todo if dont try catch it will return 500. Should always return 400 or 422.
        try {
            $accessToken = Arr::get($params, 'token');

            /*
             * @var ?SocialUser $socialUser
             */
            if (!empty($accessToken)) {
                return Socialite::driver($providerName)->userFromToken($accessToken);
            }

            return Socialite::driver($providerName)->stateless()->user();
        } catch (ClientException $e) {
            abort(400, $e->getMessage());
        }
    }
}
