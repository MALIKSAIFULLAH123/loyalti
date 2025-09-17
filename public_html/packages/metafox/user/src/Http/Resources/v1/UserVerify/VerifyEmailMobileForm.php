<?php

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Yup\Yup;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Models\UserVerify as Model;

/**
 * Class HomeVerifyEmailMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyEmailMobileForm extends VerifyEmailForm
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'formSchema',
                'payload' => [
                    'goBack' => true,
                ],
            ],
        ]);
    }

    protected function initialize(): void
    {
        if (empty($this->resource) || empty($this->verifiable)) {
            return;
        }

        $this->addHeader(['showRightHeader' => false])->component('FormHeader');
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('resend_email_description')
                ->plainText(__p('user::web.resend_email_description')),
            Builder::numberCode('verification_code')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(6, __p('user::phrase.verification_code_must_be_a_number_with_six_digits'))
                        ->matchesAsNumeric(__p('user::phrase.verification_code_must_be_a_number_with_six_digits'), false)
                        ->setError('required', __p('user::phrase.verification_code_is_a_required_field'))
                ),
            Builder::customButton('resend')
                ->label(__p('user::web.did_not_receive_resend_email'))
                ->variant('link')
                ->sizeNormal()
                ->customAction([
                    'type'    => 'user/verify/resend',
                    'payload' => [
                        'module_name'   => 'user',
                        'resource_name' => 'user_verify',
                        'action_name'   => 'resend',
                        'data'          => $this->getValue(),
                    ],
                ]),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('user::phrase.verify'))
                    ->disableWhenClean()
            );
    }
}
