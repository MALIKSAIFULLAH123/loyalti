<?php

namespace MetaFox\Group\Http\Resources\v1\CustomSection\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Traits\CreateSectionFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class EditSectionForm
 * @ignore
 * @codeCoverageIgnore
 */
class EditSectionForm extends AbstractForm
{
    use CreateSectionFormTrait;

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/profile/section/' . $this->resource?->id)
            ->asPut()
            ->setValue($this->getValues());
    }

    public function isEdit(): bool
    {
        return true;
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_GROUP;
    }
}
