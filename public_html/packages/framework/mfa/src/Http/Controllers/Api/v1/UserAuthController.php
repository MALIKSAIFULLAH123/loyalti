<?php

namespace MetaFox\Mfa\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Mfa\Http\Requests\v1\UserAuth\AuthRequest;
use MetaFox\Mfa\Http\Requests\v1\UserAuth\FormRequest;
use MetaFox\Mfa\Http\Requests\v1\UserAuth\RemoveAuthenticationRequest;
use MetaFox\Mfa\Http\Requests\v1\UserAuth\ResendVerificationAuthRequest;
use MetaFox\Mfa\Http\Resources\v1\UserService\AbstractAuthForm;
use MetaFox\Mfa\Repositories\UserAuthTokenRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\User\Admin\UserItem;
use MetaFox\User\Models\User;
use Prettus\Validator\Exceptions\ValidatorException;
use RuntimeException;

/**
 * Class UserAuthController.
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @ignore
 */
class UserAuthController extends ApiController
{
    /**
     * UserServiceController Constructor.
     *
     * @param UserServiceRepositoryInterface $repository
     */
    public function __construct(
        protected UserServiceRepositoryInterface   $repository,
        protected UserAuthTokenRepositoryInterface $userAuthTokenRepository,
    ) {
    }

    /**
     * Setup service form.
     *
     * @param FormRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function form(FormRequest $request): JsonResponse
    {
        $params        = $request->validated();
        $mfaToken      = Arr::get($params, 'mfa_token', '');
        $service       = Arr::get($params, 'service');
        $resolution    = MetaFox::getResolution();
        $userAuthToken = $this->userAuthTokenRepository->findByTokenValue($mfaToken);
        $options       = $this->repository->getActivatedServicesForForm($userAuthToken->user, true);

        if (count($options) === 1) {
            $service = Arr::first(Arr::pluck($options, 'value'));
        }

        try {
            $form = Mfa::loadServiceSelectionForm($mfaToken, $resolution);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage());
        }

        if (null != $service) {
            $form = Mfa::loadAuthForm($mfaToken, $service, $resolution);
        }

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if ($form instanceof AbstractAuthForm && count($options) === 1) {
            $form->setPreviousProcessChildId(null);
        }

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    /**
     * Auth user.
     *
     * @param AuthRequest $request
     *
     * @return JsonResponse|mixed
     */
    public function auth(AuthRequest $request)
    {
        try {
            $response = Mfa::authenticate($request);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }

        return is_array($response) ? $this->success($response) : $response;
    }

    public function resendVerificationAuth(ResendVerificationAuthRequest $request): JsonResponse
    {
        Mfa::resendVerificationAuth($request);

        return $this->success([], [], __p('mfa::phrase.verification_code_successfully'));
    }

    public function removeAuthentication(RemoveAuthenticationRequest $request): JsonResponse
    {
        $params = $request->validated();

        /** @var ?User $owner */
        $owner = User::query()->findOrFail($params['user_id']);

        $services = Arr::get($params, 'services', []);
        foreach ($services as $service) {
            Mfa::deactivate($owner, $service);
        }

        return $this->success(new UserItem($owner), [], __p('user::phrase.user_removed_authentication_successfully'));
    }
}
