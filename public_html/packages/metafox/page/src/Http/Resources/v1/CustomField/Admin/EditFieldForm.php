<?php

namespace MetaFox\Page\Http\Resources\v1\CustomField\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Traits\CreateFieldFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class EditFieldForm
 * @ignore
 * @codeCoverageIgnore
 */
class EditFieldForm extends AbstractForm
{
    use CreateFieldFormTrait;

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.profile.field.update', [
                'field' => $this->resource?->id,
            ]))
            ->asPut()
            ->setValue($this->getValues());
    }

    public function getUserType(): string
    {
        return CustomField::SECTION_TYPE_PAGE;
    }

    public function isEdit(): bool
    {
        return true;
    }
}
