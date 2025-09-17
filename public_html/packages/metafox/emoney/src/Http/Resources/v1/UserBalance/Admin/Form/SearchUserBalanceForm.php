<?php

namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\Form;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\User as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchUserBalanceForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchUserBalanceForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/admincp/emoney/user-balance')
            ->asGet()
            ->acceptPageParams(['full_name', 'sort', 'sort_type'])
            ->submitAction('@formAdmin/search/SUBMIT');
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('full_name')
                    ->forAdminSearchForm()
                    ->maxLength(255)
                    ->label(__p('user::phrase.display_name'))
                    ->yup(
                        Yup::string()
                    ),
                Builder::submit()
                    ->label(__p('core::phrase.search'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->forAdminSearchForm()
                    ->sizeMedium(),
        );
    }
}
