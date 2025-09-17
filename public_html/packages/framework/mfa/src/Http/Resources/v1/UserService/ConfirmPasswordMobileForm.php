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
 * Class ConfirmPasswordMobileForm.
 * @property ?Model $resource
 */
class ConfirmPasswordMobileForm extends ConfirmPasswordForm
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

    protected function prepare(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->title(__p('mfa::phrase.enter_password'))
            ->action(apiUrl('mfa.user.service.password'))
            ->asPost()
            ->setValue([
                'resolution' => 'mobile',
                'service'    => $this->resource->service,
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
                ->plainText(__p('mfa::phrase.for_your_security_you_must_re_enter_your_password_to_continue')),
            Builder::password('password')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.password'))
                ->required()
                ->yup(
                    Yup::string()->required(__p('validation.password_field_validation_required'))
                ),
        );
        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('core::phrase.submit'))
                ->disableWhenClean()
        );
    }
}
