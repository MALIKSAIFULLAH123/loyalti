<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Traits\MfaFieldTrait;

/**
 * Class EditPhoneNumberMobileForm.
 *
 * @property ?User $resource
 */
class EditPhoneNumberMobileForm extends AbstractForm
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
                'type' => 'formSchema',
            ],
        ]);
    }

    protected function prepare(): void
    {
        $this
            ->title(__p('core::phrase.phone_number'))
            ->asPatch()
            ->action(url_utility()->makeApiUrl('/account/setting/phone-number'))
            ->setValue([
                'phone_number' => $this->resource->phone_number,
                'resolution'   => 'mobile',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields($this->getPhoneField());
    }

    protected function getPhoneField(): AbstractField
    {
        switch ($this->isVerifyAfterChanging()) {
            case true :
                $phoneField = Builder::phoneNumber('phone_number')
                    ->variant('standard');

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
