<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchUserRelationForm.
 * @property Model $resource
 */
class SearchUserRelationForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/user/relation/browse')
            ->acceptPageParams([
                'q',
            ])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->title(__p('core::phrase.edit'))
            ->setValue([
                'sort' => SortScope::SORT_DEFAULT,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal();

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.title')),
        );

        $basic->addFields(
            Builder::submit()
                ->forAdminSearchForm(),
        );
    }
}
