<?php

namespace MetaFox\User\Traits;

use MetaFox\Form\AbstractField;
use MetaFox\Mfa\Models\Service;
use MetaFox\User\Models\User;
use MetaFox\Yup\Shape;
use MetaFox\Yup\Yup;

/**
 * @property User $resource
 */
trait MfaFieldTrait
{
    public function applyMfaRequiredEmailField(AbstractField $field): void
    {
        $this->addRequiredToField($field, Service::EMAIL_SERVICE);
    }

    public function applyMfaRequiredPhoneField(AbstractField $field): void
    {
        $this->addRequiredToField($field, Service::SMS_SERVICE);
    }

    protected function addRequiredToField(AbstractField $field, string $service): void
    {
        if (!$this->isServiceEnabled($service)) {
            return;
        }

        $field->required();
        $this->addRequiredValidator($field, $service);
    }

    protected function addRequiredValidator(AbstractField $field, string $service): void
    {
        $yup = $field->getValidation();

        if (!$yup instanceof Shape) {
            $yup = Yup::string();
        }

        $yup->required()
            ->setError('required', __p(
                'user::phrase.mfa_service_requires_this_field',
                ['service' => __p(sprintf('mfa::phrase.%s_provider_title', $service))]
            ));

        $field->yup($yup);
    }

    protected function isServiceEnabled(string $service): bool
    {
        return app('events')->dispatch('user.user_has_enabled_service', [$this->userMfaResource(), $service], true);
    }

    protected function userMfaResource(): User
    {
        return $this->resource;
    }
}
