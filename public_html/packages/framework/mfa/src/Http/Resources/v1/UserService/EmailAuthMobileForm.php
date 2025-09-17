<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Mfa\Models\UserService as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EmailAuthMobileForm.
 * @property ?Model $resource
 */
class EmailAuthMobileForm extends EmailAuthForm
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type' => 'formSchema',
            ],
        ]);
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->addHeader(['showRightHeader' => false])->component('FormHeader');
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('email_was_sent')
                ->plainText(__p('mfa::phrase.email_was_sent')),
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
                        'resource_name' => 'user_auth',
                        'action_name'   => 'resendVerificationAuth',
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
