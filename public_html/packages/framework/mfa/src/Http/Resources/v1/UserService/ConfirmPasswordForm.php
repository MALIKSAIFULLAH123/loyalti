<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\MultiStepFormTrait;
use MetaFox\Mfa\Models\UserService as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AuthenticatorAuthForm.
 * @property ?Model $resource
 */
class ConfirmPasswordForm extends AbstractForm
{
    use MultiStepFormTrait;

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'mfa_user_service_form',
                    'processChildId'         => 'mfa_user_service_password_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ]);
    }

    public function __construct(?Model $resource = null, protected string $resolution = 'web')
    {
        parent::__construct($resource);
    }

    protected function prepare(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->title(__p('mfa::phrase.enter_password'))
            ->action(apiUrl('mfa.user.service.password'))
            ->asPost()
            ->setValue([
                'resolution' => $this->resolution,
                'service'    => $this->resource->service,
            ]);
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::password('password')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.password'))
                ->required()
                ->description(__p('mfa::phrase.for_your_security_you_must_re_enter_your_password_to_continue'))
                ->yup(
                    Yup::string()->required(__p('validation.password_field_validation_required'))
                ),
        );
        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('core::phrase.submit'))
        );
    }
}
