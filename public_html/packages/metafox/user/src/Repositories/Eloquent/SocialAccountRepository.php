<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialUser;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Mfa\Support\Mfa;
use MetaFox\Platform\Contracts\User as ContractsUser;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\SocialAccount;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;

/**
 * Class SocialAccountRepository.
 *
 * @property SocialAccount $model
 */
class SocialAccountRepository extends AbstractRepository implements SocialAccountRepositoryInterface
{
    protected const CHANGE_PASSWORD_REDIRECT_URL        = '/user/password/update';
    protected const CHANGE_PASSWORD_REDIRECT_URL_MOBILE = '/settings/changePassword';

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return SocialAccount::class;
    }

    public function findSocialAccount(string $providerUserId, string $provider, $with = []): ?SocialAccount
    {
        /** @var SocialAccount $socialAccount */
        $socialAccount = SocialAccount::query()
            ->with($with)
            ->where('provider_user_id', '=', $providerUserId)
            ->where('provider', '=', $provider)
            ->first();

        if (!$socialAccount instanceof SocialAccount) {
            return null;
        }

        return $socialAccount;
    }

    public function createSocialAccount(SocialUser $socialUser, string $provider, array $params): SocialAccount
    {
        // Save to social account.
        $socialAccount                   = new SocialAccount();
        $socialAccount->provider_user_id = $socialUser->getId();
        $socialAccount->provider         = $provider;
        $socialAccount->hash             = md5($socialUser->getId() . $provider);

        $socialAccount->extra = [
            'is_register'    => true,
            'request_params' => $params,
            'social_user'    => serialize($socialUser),
        ];

        $socialAccount->save();
        $socialAccount->refresh();

        return $socialAccount;
    }

    public function findOrCreateSocialAccount(SocialUser $socialUser, string $providerName, array $params): SocialAccount
    {
        $socialAccount = $this->findSocialAccount($socialUser->getId(), $providerName, ['user']);

        if (!$socialAccount instanceof SocialAccount) {
            $socialAccount = $this->createSocialAccount($socialUser, $providerName, $params);
        }

        $socialAccount->hash_expired_at = Carbon::now()->addMinutes(SocialAccount::HASH_EXPIRE_TIME);

        $socialAccount->save();

        return $socialAccount;
    }

    public function findSocialAccountByHash(string $hash): SocialAccount
    {
        $socialAccount = $this->getModel()->newQuery()
            ->where('hash', $hash)
            ->whereNull('user_id')
            ->firstOrFail();

        if (Carbon::now()->gt($socialAccount->hash_expired_at)) {
            throw new \RuntimeException(__p('user::phrase.your_session_with_the_social_account_has_expired'));
        }

        return $socialAccount;
    }

    public function deleteSocialAccountsByUserId(int $userId)
    {
        $this->deleteWhere([
            'user_id' => $userId,
        ]);
    }

    /**
     * @param SocialAccount $socialAccount
     *
     * @return User
     */
    public function handleUserAccount(SocialAccount $socialAccount): User
    {
        $socialUser = $socialAccount->social_user;

        if (!$socialUser instanceof SocialUser) {
            throw new ModelNotFoundException();
        }

        // This is a new user try to sign up by social account, check if email already exists.
        $socialEmail = $socialUser->getEmail();
        $user        = $socialEmail ? $this->userRepository()->findUserByEmail($socialEmail) : null;

        if ($user !== null) {
            if (!empty($socialAccount->extra)) {
                $socialAccount->updateQuietly(['extra' => null]);
            }

            return $user;
        }

        if (!Settings::get('user.allow_user_registration')) {
            abort(403, __p('user::phrase.user_registration_is_disabled'));
        }

        $approveStatus = MetaFoxConstant::STATUS_APPROVED;

        if (Settings::get('user.approve_users')) {
            $approveStatus = MetaFoxConstant::STATUS_PENDING_APPROVAL;
        }

        $userName = $socialAccount->provider . Carbon::now()->timestamp . Str::random(4);

        /** @var User $user */
        $user = $this->userRepository()->createUser([
            'full_name'      => $this->getUserFullName($socialUser, $socialAccount->request_params),
            'user_name'      => $userName,
            'email'          => $this->getUserEmail($socialEmail),
            'password'       => '',
            'invite_code'    => $socialAccount->invite_code,
            'approve_status' => $approveStatus,
        ]);

        $this->handleUserAvatar($user, $socialUser);

        if ($user->shouldVerifyEmailAddress()) {
            $user->markEmailAsVerified();
        }

        $user->markAsVerified();

        // Refresh to get full data.
        $user->refresh();

        app('events')->dispatch('user.registered', [$user]);

        return $user;
    }

    public function assignUserToSocialAccount(SocialAccount $socialAccount): SocialAccount
    {
        $user = $this->handleUserAccount($socialAccount);

        unset($socialAccount->invite_code);

        $socialAccount->update([
            'user_id'         => $user->entityId(),
            'hash'            => null,
            'hash_expired_at' => null,
        ]);

        return $socialAccount->refresh();
    }

    public function processUserSignIn(SocialAccount $socialAccount): array
    {
        $user   = $socialAccount->user;
        $params = $socialAccount->request_params;

        app('events')->dispatch('user.signing_in', [$user, $params]);

        $this->userRepository()->validateStatuses($user);

        $response = app('events')->dispatch('user.request_mfa_token', [$user], true);

        if ($response) {
            /*
             * @deprecated Remove in 5.2
             */
            if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
                return $response;
            }

            return [
                'redirect_url' => Mfa::MFA_REDIRECT_URL . '?' . http_build_query($response),
            ];
        }

        app('events')->dispatch('user.signed_in', [$user, $params]);

        $response = [
            'access_token' => $user->createToken('social_login')->accessToken,
        ];

        if ($socialAccount->isRegister() && Settings::get('socialite.prompt_users_to_set_passwords', true)) {
            $changePasswordPath       = MetaFox::isMobile() ? self::CHANGE_PASSWORD_REDIRECT_URL_MOBILE : self::CHANGE_PASSWORD_REDIRECT_URL;
            $response['redirect_url'] = $changePasswordPath . '?' . http_build_query(['source' => 'social_register']);
        }

        if (!empty($socialAccount->extra)) {
            $socialAccount->updateQuietly(['extra' => null]);
        }

        return $response;
    }

    protected function getUserFullName(SocialUser $socialUser, array $params): string
    {
        return $socialUser->getName() ?? Arr::get($params, 'display_name', $socialUser->getEmail()) ?? '';
    }

    protected function getUserEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    protected function handleUserAvatar(ContractsUser $user, SocialUser $socialUser): void
    {
        try {
            $avatarUrl = $socialUser->__get('avatar_original') ?? $socialUser->getAvatar();
            if (empty($avatarUrl)) {
                return;
            }

            $response = Http::get($avatarUrl);
            if (!$response->ok()) {
                return;
            }

            $tempFile = sprintf('%s.%s', tempnam(sys_get_temp_dir(), 'metafox'), $this->getFileExtensionFromURL($avatarUrl) ?? 'jpg');
            file_put_contents($tempFile, $response->body());

            $uploadedFile = UploadFile::pathToUploadedFile($tempFile);
            if (!$uploadedFile instanceof UploadedFile) {
                return;
            }

            $this->userRepository()->createAvatarFromSignup($user, $uploadedFile, []);
        } catch (\Exception $exception) {
            Log::error('Upload User Avatar Error: ' . $exception->getMessage());

            return;
        }
    }

    private function getFileExtensionFromURL(string $url): ?string
    {
        $ext = File::extension($url);
        $ext = parse_url($ext, PHP_URL_PATH);
        $ext = pathinfo($ext, PATHINFO_BASENAME);

        return Str::afterLast($ext, '.') ?: null;
    }

    private function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    public function isRequiredVerifyInviteCode(bool $checkUserExisted = true, string $socialEmail = ''): bool
    {
        if (!Settings::get('invite.invite_only', false)) {
            return false;
        }

        if (!$checkUserExisted) {
            return true;
        }

        $user = $this->userRepository()->findUserByEmail($socialEmail);

        return !$user instanceof User;
    }
}
