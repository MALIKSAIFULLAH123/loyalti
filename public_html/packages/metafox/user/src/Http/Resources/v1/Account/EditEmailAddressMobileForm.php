<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Traits\MfaFieldTrait;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditEmailAddressMobileForm.
 * @property ?User $resource
 */
class EditEmailAddressMobileForm extends AbstractForm
{
    use MfaFieldTrait;

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type' => 'formSchema',
            ],
        ]);
    }

    protected function prepare(): void
    {
        $value = $this->resource ? [
            'email'      => $this->resource->email,
            'resolution' => 'mobile',
        ] : null;

        $this
            ->title(__p('core::phrase.email_address'))
            ->asPatch()
            ->action(url_utility()->makeApiUrl('/account/setting/email'))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields($this->getEmailField());
    }

    protected function getEmailField(): AbstractField
    {
        $emailField = Builder::text('email')
            ->required()
            ->label(__p('core::phrase.email_address'))
            ->placeholder(__p('core::phrase.email_address'))
            ->yup(
                Yup::string()
                    ->email(__p('validation.invalid_email_address'))
                    ->required()
            );

        $this->applyMfaRequiredEmailField($emailField);

        return $emailField;
    }
}
