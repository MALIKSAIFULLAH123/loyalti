<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Traits\MfaFieldTrait;
use MetaFox\Yup\Yup;

/**
 * Class EditEmailAddressForm.
 * @property ?User $resource
 */
class EditEmailAddressForm extends AbstractForm
{
    use MfaFieldTrait;

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'user_change_email_form',
                    'processChildId'         => 'user_get_update_email_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ]);
    }

    protected function prepare(): void
    {
        $value = $this->resource ? [
            'email' => $this->resource->email,
        ] : null;

        if (Settings::get('user.verify_after_changing_email')) {
            $this->title(__p('core::phrase.email_address'));
        }

        $this->asPatch()
            ->action(url_utility()->makeApiUrl('/account/setting/email'))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields($this->getEmailField());

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }

    protected function getEmailField(): AbstractField
    {
        $emailField = Builder::text('email')
            ->autoComplete('off')
            ->marginNormal()
            ->label(__p('core::phrase.email_address'))
            ->placeholder(__p('core::phrase.email_address'))
            ->required()
            ->fullWidth()
            ->yup(
                Yup::string()
                    ->email(__p('validation.field_must_be_a_valid_email'))
                    ->required()
            );

        $this->applyMfaRequiredEmailField($emailField);

        return $emailField;
    }
}
