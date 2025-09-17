<?php

namespace MetaFox\User\Support;

use Illuminate\Support\Arr;
use MetaFox\User\Contracts\Support\ActionServiceInterface;
use MetaFox\User\Contracts\Support\ActionServiceManagerInterface;
use MetaFox\User\Contracts\UserVerifySupportContract;
use MetaFox\User\Models\UserVerify;
use RuntimeException;

class UserVerifySupport implements UserVerifySupportContract
{
    public const WEB_SERVICE   = 'web';
    public const ADMIN_SERVICE = 'admin';

    public const EMAIL_FIELD            = 'email';
    public const PHONE_NUMBER_FIELD     = 'phone_number';
    public const VERIFY_EMAIL_AT        = 'email_verified_at';
    public const VERIFY_PHONE_NUMBER_AT = 'phone_number_verified_at';

    public const HOME_VERIFY           = 'home';
    public const UPDATE_ACCOUNT_VERIFY = 'update_account';

    public function getVerifyAtField(string $action): string
    {
        return $action == UserVerify::ACTION_EMAIL ? self::VERIFY_EMAIL_AT : self::VERIFY_PHONE_NUMBER_AT;
    }

    public function getVerifiableField(string $action): string
    {
        return $action == UserVerify::ACTION_EMAIL ? self::EMAIL_FIELD : self::PHONE_NUMBER_FIELD;
    }

    public function getAllowedActions(string $service): array
    {
        $actions = resolve(ActionServiceManagerInterface::class)->getAllByService($service);

        return Arr::pluck($actions, 'name');
    }

    public function __call($name, $arguments)
    {
        $action = array_shift($arguments);
        if (empty($action)) {
            throw new InvalidArgumentException(__p('user::phrase.a_specific_action_is_required'));
        }

        $service = resolve(ActionServiceManagerInterface::class)->get($name, $action);
        if (!$service instanceof ActionServiceInterface) {
            throw new RuntimeException(
                __p(
                    'user::phrase.service_is_not_an_instance_of_interface',
                    ['service' => $service::class, 'interface' => ActionServiceInterface::class]
                )
            );
        }

        return $service;
    }
}
