<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Builder;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Models\UserAuthToken as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SmsAuthForm.
 *
 * @property ?Model $resource
 */
class SmsAuthForm extends AbstractAuthForm
{
    protected function getService(): string
    {
        return Service::SMS_SERVICE;
    }

    public ?string $previousProcessId = 'mfa_get_sms_authentication_form';

    protected function prepare(): void
    {
        parent::prepare();

        $this->title(__p('mfa::phrase.sms_authentication'));
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

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
                        'resource_name' => 'user_auth',
                        'action_name'   => 'resendVerificationAuth',
                        'data'          => $this->getValue(),
                    ],
                ]),
        );

        $this->buildFooter();
    }
}
