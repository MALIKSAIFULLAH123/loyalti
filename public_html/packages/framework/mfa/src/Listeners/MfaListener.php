<?php

namespace MetaFox\Mfa\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Mfa\Contracts\ServiceManagerInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

class MfaListener
{
    public function requestMfaToken($user, string $resolution = MetaFoxConstant::RESOLUTION_WEB)
    {
        if ($user && Mfa::hasMfaEnabled($user)) {
            return [
                'mfa_token' => Mfa::requestMfaToken($user, $resolution),
            ];
        }
    }

    public function validateForPassportPasswordGrant($user, $input)
    {
        if ($user && Mfa::hasMfaEnabled($user)) {
            return Mfa::isAuthenticated($user, $input);
        }
    }

    public function hasMfaEnabled($user)
    {
        return (bool) Mfa::hasMfaEnabled($user);
    }

    public function hasMfaServiceEnabled(User $user, string $service): bool
    {
        return Mfa::hasMfaServiceEnabled($user, $service);
    }

    public function validateMfaFieldForRequest(User $user, array $params): void
    {
        $data  = [];
        $rules = [];

        $userServiceRepository = resolve(UserServiceRepositoryInterface::class);
        $serviceManager        = resolve(ServiceManagerInterface::class);

        $activatedServices = $userServiceRepository->getActivatedServices($user);

        foreach ($activatedServices as $service) {
            $handler        = $serviceManager->get($service->service);
            $validateFields = $handler->validateField();

            foreach ($validateFields as $field) {
                if (!Arr::has($params, $field)) {
                    continue;
                }

                $data[$field]  = Arr::get($params, $field);
                $rules[$field] = ['required'];
            }
        }

        $validator = Validator::make($data, $rules);

        $validator->validate();
    }
}
