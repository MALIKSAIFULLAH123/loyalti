<?php

namespace MetaFox\Importer\Http\Resources\v1\Log\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Importer\Models\Bundle as Model;

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
class SearchLogForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/importer/log/browse')
            ->acceptPageParams(['level', 'q'])
            ->title(__p('importer::phrase.browse_bundle'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::choice('level')
                    ->forAdminSearchForm()
                    ->label(__p('log::msg.level'))
                    ->options([
                        ['value' => 'INITIAL', 'label' => 'INITIAL'],
                        ['value' => 'DEBUG', 'label' => 'DEBUG'],
                        ['value' => 'INFO', 'label' => 'INFO'],
                        ['value' => 'WARNING', 'label' => 'WARNING'],
                        ['value' => 'ERROR', 'label' => 'ERROR'],
                        ['value' => 'EMERGENCY', 'label' => 'EMERGENCY'],
                        ['value' => 'ALERT', 'label' => 'ALERT'],
                    ]),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }
}
