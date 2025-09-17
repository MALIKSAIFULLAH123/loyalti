<?php

namespace MetaFox\Storage\Http\Resources\v1\Asset\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Storage\Models\Asset as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditAssetForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchAssetForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.storage.asset.index'))
            ->acceptPageParams(['q', 'module_id'])
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.search_dot')),
                Builder::selectPackageAlias('module_id')
                ->forAdminSearchForm(),
                Builder::submit()
                ->forAdminSearchForm(),
            );
    }
}
