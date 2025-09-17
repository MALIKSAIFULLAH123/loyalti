<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use MetaFox\User\Exceptions\VerifyCodeException;
use MetaFox\User\Http\Requests\v1\UserVerify\ResendLinkRequest;
use MetaFox\User\Http\Requests\v1\UserVerify\ResendRequest;
use MetaFox\User\Http\Requests\v1\UserVerify\VerifyFormRequest;
use MetaFox\User\Http\Requests\v1\UserVerify\VerifyRequest;
use MetaFox\User\Http\Resources\v1\User\UserSimple;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Repositories\UserVerifyRepositoryInterface;
use MetaFox\User\Support\Facades\UserVerify as UserVerifyFacade;
use MetaFox\User\Support\UserVerifySupport;
use Prettus\Validator\Exceptions\ValidatorException;
use RuntimeException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\UserVerifyController::$controllers;.
 */

/**
 * Class UserVerifyController.
 * @codeCoverageIgnore
 * @ignore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserVerifyController extends ApiController
{
    /**
     * @var UserVerifyRepositoryInterface
     */
    private UserVerifyRepositoryInterface $repository;

    /**
     * UserVerifyController Constructor.
     *
     * @param UserVerifyRepositoryInterface $repository
     */
    public function __construct(UserVerifyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param VerifyRequest $request
     *
     * @return JsonResponse
     * @throws VerifyCodeException
     */
    public function verify(VerifyRequest $request): JsonResponse
    {
        $params = $request->validated();
        $action = Arr::get($params, 'action');
        $code   = Arr::get($params, 'verification_code');

        $user = UserVerifyFacade::web($action)->verify($code);
        $data = $user ? new UserSimple($user) : [];

        $message = __p('user::web.your_email_has_been_verified_successfully');
        if ($action == UserVerify::ACTION_PHONE_NUMBER) {
            $message = __p('user::web.your_phone_number_has_been_verified_successfully');
        }

        $redirectUrl = Settings::get('user.redirect_after_signup', '/');
        $redirectUrl = empty($redirectUrl) ? '/' : $redirectUrl;

        $actionMeta = new ActionMeta();
        $actionMeta->nextAction()
            ->type('@redirectTo')
            ->payload(PayloadActionMeta::payload()->url($redirectUrl));

        return $this->success($data, $actionMeta->toArray(), $message);
    }

    /**
     * @param string $hash
     *
     * @return JsonResponse
     * @throws VerifyCodeException
     * @deprecated Need remove for some next version
     */
    public function verifyLink(Request $request, string $hash): JsonResponse
    {
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.6', '>')) {
            throw new RuntimeException(__p('user::phrase.does_not_support_this_mobile_app_version'));
        }

        $verify = $this->repository->findByField('hash_code', $hash)->first();
        if (!$verify instanceof UserVerify) {
            $message = json_encode([
                'title' => __p('user::phrase.verification_code_not_found'),
            ]);
            abort(400, $message);
        }

        $user = UserVerifyFacade::web($verify->action)->verify(null, $hash);
        $data = $user ? new UserSimple($user) : [];

        $message = __p('user::web.your_email_has_been_verified_successfully');
        if ($verify->action == UserVerify::ACTION_PHONE_NUMBER) {
            $message = __p('user::web.your_phone_number_has_been_verified_successfully');
        }

        $redirectUrl = Settings::get('user.redirect_after_signup', '/');

        $actionMeta = new ActionMeta();
        $actionMeta->nextAction()
            ->type('@redirectTo')
            ->payload(PayloadActionMeta::payload()->url($redirectUrl));

        return $this->success($data, $actionMeta->toArray(), $message);
    }

    /**
     * @param ResendRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function resend(ResendRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->resend($params);

        $message = Arr::has($params, 'phone_number')
            ? __p('user::phrase.verification_message_sent')
            : __p('user::phrase.verification_email_sent');

        return $this->success([], [], $message);
    }

    /**
     * @param ResendLinkRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @deprecated Need remove for some next version
     */
    public function resendLink(ResendLinkRequest $request): JsonResponse
    {
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.6', '>')) {
            throw new RuntimeException(__p('user::phrase.does_not_support_this_mobile_app_version'));
        }

        $params = $request->validated();

        if (Arr::has($params, 'phone_number')) {
            /** @var User $user */
            $user = User::query()->where('phone_number', '=', $params['phone_number'])->first();

            $this->repository->resendLink($user, UserVerify::ACTION_PHONE_NUMBER, $user->phone_number);

            return $this->success([], [], __p('user::phrase.verification_message_sent'));
        }

        /** @var User $user */
        $user = User::query()->where('email', '=', $params['email'])->first();

        $this->repository->resendLink($user, UserVerify::ACTION_EMAIL, $user->email);

        return $this->success([], [], __p('user::phrase.verification_email_sent'));
    }

    public function form(VerifyFormRequest $request): JsonResponse
    {
        $params          = $request->validated();
        $action          = Arr::get($params, 'action');
        $resolution      = Arr::get($params, 'resolution', 'web');
        $verifiableField = UserVerifyFacade::getVerifiableField($action);
        $verifiableValue = Arr::get($params, $verifiableField);

        $user = User::find(Arr::get($params, 'user_id'));

        $form = UserVerifyFacade::web($action)
            ->verifyForm($user, $verifiableValue, UserVerifySupport::HOME_VERIFY, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }
}
