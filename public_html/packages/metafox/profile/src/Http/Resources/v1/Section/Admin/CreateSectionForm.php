<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Profile\Models\Section as Model;
use MetaFox\Profile\Traits\CreateSectionFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSectionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSectionForm extends AbstractForm
{
    use CreateSectionFormTrait;
}
