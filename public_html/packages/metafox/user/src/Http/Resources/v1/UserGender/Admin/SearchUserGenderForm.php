<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\UserGender as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchUserGenderForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName user.user_gender.search
 */
class SearchUserGenderForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('user::phrase.manage_genders'))
            ->action('/user/user-gender')
            ->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }
}
