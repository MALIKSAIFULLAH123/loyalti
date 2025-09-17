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
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Traits\MfaFieldTrait;

/**
 * Class EditPhoneNumberForm.
 *
 * @property ?User $resource
 */
class EditPhoneNumberForm extends AbstractForm
{
    use MfaFieldTrait;

    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        /** @var Model $context */
        $context        = user();
        $this->resource = $context;

        policy_authorize(UserPolicy::class, 'updateSetting', $context, $this->resource);

        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'user_change_phone_number_form',
                    'processChildId'         => 'user_get_update_phone_number_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ]);
    }

    protected function prepare(): void
    {
        if ($this->isVerifyAfterChanging()) {
            $this->title(__p('core::phrase.phone_number'));
        }

        $this->asPatch()
            ->action(url_utility()->makeApiUrl('/account/setting/phone-number'))
            ->setValue([
                'phone_number' => $this->resource->phone_number,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields($this->getPhoneField());

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }

    protected function getPhoneField(): AbstractField
    {
        switch ($this->isVerifyAfterChanging()) {
            case true :
                $phoneField = Builder::phoneNumber('phone_number');

                $this->applyMfaRequiredPhoneField($phoneField);
                break;
            default:
                $phoneField = Builder::text('phone_number')
                    ->label(__p('core::phrase.phone_number'));
        }

        return $phoneField;
    }

    protected function isVerifyAfterChanging(): bool
    {
        return Settings::get('user.verify_after_changing_phone_number') ?? false;
    }
}
