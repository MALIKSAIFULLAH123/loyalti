<?php

namespace MetaFox\Group\Http\Resources\v1\CustomSection\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Traits\CreateSectionFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateSectionForm
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSectionForm extends AbstractForm
{
    use CreateSectionFormTrait;

    public function getUserType(): string
    {
        return CustomField::SECTION_TYPE_GROUP;
    }
}
