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
 * Class AuthenticatorAuthMobileForm.
 * @property ?Model $resource
 */
class AuthenticatorAuthMobileForm extends AuthenticatorAuthForm
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
            Builder::typography('description')
                ->plainText(__p('mfa::phrase.authenticator_login_description')),
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
