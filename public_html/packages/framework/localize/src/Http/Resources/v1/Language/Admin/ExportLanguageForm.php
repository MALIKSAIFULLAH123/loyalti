<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Localize\Models\Language as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ExportLanguageForm.
 * @property Model $resource
 */
class ExportLanguageForm extends AbstractForm
{
    /** @var bool */
    protected bool $isEdit = false;

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('language/' . $this->resource->id)
            ->asPost()
            ->setValue([
                //
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields();

        $this->addDefaultFooter(true);
    }
}
