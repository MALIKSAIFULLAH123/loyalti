<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchFieldForm.
 *
 * @property Model $resource
 */
class SearchSectionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/profile/field')
            ->acceptPageParams(['title', 'active'])
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('title')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.name')),
                Builder::choice('active')
                    ->forAdminSearchForm()
                    ->label(__p('profile::phrase.active'))
                    ->options($this->getActiveOptions()),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }

    private function getActiveOptions(): array
    {
        return
            [
                [
                    'label' => __p('profile::phrase.active'),
                    'value' => 1,
                ],
                [
                    'label' => __p('profile::phrase.inactive'),
                    'value' => 0,
                ],
            ];
    }

}
