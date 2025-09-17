<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DeactivateServiceMobileForm.
 */
class DeactivateServiceMobileForm extends DeactivateServiceForm
{
    protected function initialize(): void
    {
        $this->addHeader(['showRightHeader' => false])->component('FormHeader');

        parent::initialize();
    }

    protected function handlePasswordFields(): array
    {
        return [
            Builder::password('password')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.password'))
                ->required()
                ->description(__p('mfa::phrase.for_your_security_you_must_re_enter_your_password_to_continue'))
                ->yup(
                    Yup::string()->required(__p('validation.password_field_validation_required'))
                ),
        ];
    }
}
