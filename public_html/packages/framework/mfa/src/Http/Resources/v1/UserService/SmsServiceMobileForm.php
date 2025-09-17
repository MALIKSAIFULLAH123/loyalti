<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Mfa\Models\UserService as Model;
use MetaFox\Mfa\Traits\SetupMobileFormTrait;
use MetaFox\Yup\Yup;
use MetaFox\Form\Mobile\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SmsServiceMobileForm.
 * @property ?Model $resource
 */
class SmsServiceMobileForm extends SmsServiceForm
{
    use SetupMobileFormTrait;

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->addHeader(['showRightHeader' => false])->component('FormHeader');
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('sms_was_sent')
                ->plainText(__p('mfa::phrase.sms_was_sent')),
            Builder::numberCode('verification_code')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(6, __p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'))
                        ->matchesAsNumeric(__p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'), false)
                        ->setError('required', __p('mfa::phrase.authenticator_code_is_a_required_field'))
                ),
            Builder::customButton('resend')
                ->label(__p('mfa::phrase.didnt_receive_a_verification_code_resend'))
                ->variant('link')
                ->sizeNormal()
                ->customAction([
                    'type'    => 'authenticator/mfa/resend',
                    'payload' => [
                        'module_name'   => 'mfa',
                        'resource_name' => 'user_service',
                        'action_name'   => 'resendVerificationSetup',
                        'data'          => $this->getValue(),
                    ],
                ]),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('mfa::phrase.verify'))
                    ->disableWhenClean()
            );
    }
}
