<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DeactivateServiceForm.
 */
class DeactivateServiceForm extends AbstractForm
{
    private string $service;

    public function boot(Request $request): void
    {
        $this->service = $request->get('service', '');
    }

    protected function prepare(): void
    {
        $this->title(__p('mfa::phrase.turn_off_two_factor_authentication_label'))
            ->action(apiUrl('mfa.user.service.deactivate'))
            ->asDelete()
            ->setValue([
                'service' => $this->service,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('description')
                ->plainText(__p('mfa::phrase.turn_off_two_factor_authentication_desc')),
        );

        if (Mfa::hasConfirmPassword(user())) {
            $basic->addFields(
                ...$this->handlePasswordFields(),
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.delete'))
            );
    }

    /**
     * handlePasswordFields.
     *
     * @return array<AbstractField>
     */
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
