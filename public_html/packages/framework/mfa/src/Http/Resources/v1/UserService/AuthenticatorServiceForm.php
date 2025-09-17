<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mfa\Models\UserService as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AuthenticatorServiceForm.
 * @property ?Model $resource
 */
class AuthenticatorServiceForm extends AbstractForm
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'mfa_user_service_form',
                    'processChildId'         => 'mfa_user_service_setup_form',
                    'previousProcessChildId' => 'mfa_user_service_password_form',
                ],
            ],
        ]);
    }

    protected function prepare(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->title(__p('mfa::phrase.two_factor_authentication'))
            ->action(apiUrl('mfa.user.service.activate'))
            ->asPost()
            ->setValue([
                'service' => $this->resource->service,
            ]);
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $basic  = $this->addBasic();
        $secret = $this->resource->value;
        $extra  = $this->resource->extra;

        $basic->addFields(
            Builder::typography('setup_step_1')
                ->plainText(__p('mfa::phrase.authenticator_service_setup_step_1')),
            Builder::typography('setup_step_2')
                ->plainText(__p('mfa::phrase.authenticator_service_setup_step_2')),
            Builder::typography('setup_step_3')
                ->plainText(__p('mfa::phrase.authenticator_service_setup_step_3')),
            Builder::qrCode('qr_code')
                ->label(__p('mfa::phrase.authenticator_qr_code_description'))
                ->description(trim(chunk_split($secret, 4, ' ')))
                ->content(Arr::get($extra, 'qr_code', '')),
            Builder::typography('setup_step_4')
                ->plainText(__p('mfa::phrase.authenticator_service_setup_step_4')),
            Builder::numberCode('verification_code')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(6, __p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'))
                        ->matchesAsNumeric(__p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'), false)
                        ->setError('required', __p('mfa::phrase.authenticator_code_is_a_required_field'))
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('mfa::phrase.verify'))
                    ->disableWhenClean()
            );
    }
}
