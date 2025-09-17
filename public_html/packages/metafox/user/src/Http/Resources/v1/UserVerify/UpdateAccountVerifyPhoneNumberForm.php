<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Form\Builder;

/**
 * Class UpdateAccountVerifyPhoneNumberForm.
 * @preload    1
 */
class UpdateAccountVerifyPhoneNumberForm extends VerifyPhoneNumberForm
{
    protected function initialize(): void
    {
        if (empty($this->resource) || empty($this->verifiable)) {
            return;
        }

        parent::initialize();
    }

    protected function buildFooter(): void
    {
        $this->addFooter()->addFields(
            Builder::cancelButton()
                ->label(__p('core::phrase.cancel'))
                ->variant('outlined'),
            Builder::submit()
                ->label(__p('user::phrase.verify'))
        );
    }
}
