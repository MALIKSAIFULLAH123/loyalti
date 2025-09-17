<?php

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\User\Models\User as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AccountPopupForm.
 *
 * @property ?Model $resource
 * @preload 1
 */
class AccountPopupForm extends LoginPopupUserForm
{
    protected function getSectionHeader(): void
    {
        $header = $this->addSection(
            Builder::section('header')
        );

        $header->addFields(
            Builder::typography('form_header')
                ->plainText(__p('user::web.add_existing_account'))
                ->variant('h3')
                ->sx([
                    'justifyContent' => 'center',
                    'display'        => 'flex',
                ])
        );
    }

    protected function getRegisterField(Section $basic): void
    {

    }

    protected function getReturnUrlField(Section $basic): void
    {

    }
}
