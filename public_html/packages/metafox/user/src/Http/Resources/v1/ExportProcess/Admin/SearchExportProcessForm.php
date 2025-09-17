<?php

namespace MetaFox\User\Http\Resources\v1\ExportProcess\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\CancelReason as Model;
use MetaFox\User\Support\User as UserSupport;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchExportProcessForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchExportProcessForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('user/export-process/browse')
            ->acceptPageParams(['q', 'status'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->label(__p('log::file.filename'))
                    ->forAdminSearchForm(),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options(UserSupport::allowedStatusExportOptions()),
                Builder::submit()->forAdminSearchForm(),
            );
    }
}
