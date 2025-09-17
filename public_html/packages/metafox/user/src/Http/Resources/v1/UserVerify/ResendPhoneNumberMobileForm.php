<?php

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Models\UserVerify as Model;

/**
 * @deprecated Need remove for some next version
 * Class ResendPhoneNumberMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ResendPhoneNumberMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('user::web.verify_your_phone_number'))
            ->asPost()
            ->action(url_utility()->makeApiUrl('user/verify/resendLink'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::phoneNumber('phone_number')
                ->placeholder(__p('core::web.enter_your_phone'))
                ->variant('standard')
                ->autoFocus(true)
                ->required(),
        );
    }
}
