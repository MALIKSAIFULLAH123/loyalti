<?php

namespace MetaFox\Page\Http\Resources\v1\CustomField\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Traits\CreateFieldFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateFieldForm
 * @ignore
 * @codeCoverageIgnore
 */
class CreateFieldForm extends AbstractForm
{
    use CreateFieldFormTrait;

    protected function getValues(): array
    {
        return [
            'type_id'         => 'main',
            'edit_type'       => CustomFieldSupport::TEXT,
            'view_type'       => 'text',
            'var_type'        => CustomFieldSupport::TYPE_STRING,
            'has_label'       => 1,
            'has_description' => 1,
            'is_active'       => 1,
        ];
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_PAGE;
    }
    
    public function getActiveField(): ?AbstractField
    {
        return Builder::checkbox('is_active')
            ->label(__p('core::phrase.is_active'));
    }
}
