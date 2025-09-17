<?php

namespace MetaFox\Notification\Http\Resources\v1\Type\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchTypeForm.
 */
class SearchTypeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.notification.type.index'))
            ->acceptPageParams(['q', 'module_id'])
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::selectPackageAlias('module_id')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.package_name')),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }
}
