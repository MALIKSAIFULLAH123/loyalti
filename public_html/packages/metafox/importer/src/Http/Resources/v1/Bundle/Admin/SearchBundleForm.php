<?php

namespace MetaFox\Importer\Http\Resources\v1\Bundle\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Importer\Models\Bundle as Model;
use MetaFox\Importer\Support\Browse\Scopes\Bundle\StatusScope;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchBundleForm.
 * @property Model $resource
 */
class SearchBundleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/importer/bundle/browse')
            ->acceptPageParams(['status', 'q'])
            ->title(__p('importer::phrase.browse_bundle'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.search')),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('importer::phrase.status'))
                    ->options(StatusScope::getStatusOptions()),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }
}
