<?php

namespace MetaFox\Mfa\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Mfa\Http\Requests\v1\UserService\ActivateRequest;
use MetaFox\Mfa\Http\Requests\v1\UserService\ConfirmPasswordRequest;
use MetaFox\Mfa\Http\Requests\v1\UserService\DeactivateRequest;
use MetaFox\Mfa\Http\Requests\v1\UserService\ResendVerificationSetupRequest;
use MetaFox\Mfa\Http\Requests\v1\UserService\SetupRequest;
use MetaFox\Mfa\Http\Resources\v1\Service\ServiceItem;
use MetaFox\Mfa\Http\Resources\v1\UserService\UserServiceItem;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class UserServiceController.
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @ignore
 */
class UserServiceController extends ApiController
{
    /**
     * @var UserServiceRepositoryInterface
     */
    private UserServiceRepositoryInterface $repository;
    private ServiceRepositoryInterface $serviceRepository;

    /**
     * UserServiceController Constructor.
     *
     * @param UserServiceRepositoryInterface $repository
     */
    public function __construct(UserServiceRepositoryInterface $repository, ServiceRepositoryInterface $serviceRepository)
    {
        $this->repository        = $repository;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Setup service form.
     *
     * @param  SetupRequest       $request
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function setup(SetupRequest $request): JsonResponse
    {
        $context    = user();
        $params     = $request->validated();
        $service    = Arr::get($params, 'service');
        $resolution = Arr::get($params, 'resolution', 'web');

        $userService = Mfa::initSetup($context, $service);
        if (Mfa::hasConfirmPassword($context)) {
            $form = Mfa::loadPasswordForm($userService, $resolution);

            return $this->success($form, $form->getMultiStepFormMeta());
        }

        $form = Mfa::loadSetupForm($userService, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    public function password(ConfirmPasswordRequest $request): JsonResponse
    {
        $context     = user();
        $params      = $request->validated();
        $service     = Arr::get($params, 'service');
        $resolution  = Arr::get($params, 'resolution', 'web');
        $userService = Mfa::initSetup($context, $service);

        $form = Mfa::loadSetupForm($userService, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }
    /**
     * Activate service.
     *
     * @param  ActivateRequest    $request
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function activate(ActivateRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $service = Arr::get($params, 'service');

        try {
            $userService = Mfa::activate($context, $service, $request->all());
        } catch (Exception $e) {
            return $this->error(__p('mfa::phrase.failed_to_activate_the_service', [
                'service' => $service,
            ]));
        }

        return $this->success(new UserServiceItem($userService), [], __p('mfa::phrase.service_has_been_activated', [
            'service' => $service,
        ]));
    }

    /**
     * Deactivate service.
     *
     * @param  DeactivateRequest  $request
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function deactivate(DeactivateRequest $request): JsonResponse
    {
        $context = user();
        $service = $request->validated('service');

        Mfa::deactivate($context, $service);

        return $this->success(
            new ServiceItem($this->serviceRepository->getServiceByName($service)),
            [],
            __p('mfa::phrase.service_has_been_deactivated', [
                'service' => $service,
            ])
        );
    }

    public function resendVerificationSetup(ResendVerificationSetupRequest $request): JsonResponse
    {
        $context = user();

        Mfa::resendVerificationSetup($context, $request);

        return $this->success([], [], __p('mfa::phrase.verification_code_successfully'));
    }
}
