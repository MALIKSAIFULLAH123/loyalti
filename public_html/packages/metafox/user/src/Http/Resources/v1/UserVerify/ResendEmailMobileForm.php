<?php

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Models\UserVerify as Model;

/**
 * @deprecated Need remove for some next version
 * Class ResendEmailMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ResendEmailMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('user::web.verify_your_email'))
            ->asPost()
            ->action(url_utility()->makeApiUrl('user/verify/resendLink'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::email('email')
                ->variant('standard')
                ->label(__p('core::phrase.email'))
                ->required()
                ->placeholder(__p('user::phrase.enter_your_email'))
                ->autoComplete('email')
                ->autoFocus(true)
                ->yup(
                    Yup::string()
                        ->email(__p('validation.invalid_email_address'))
                        ->required()
                ),
        );
    }
}
