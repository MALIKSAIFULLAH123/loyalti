<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Facade\CustomProfile;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class AdditionalInformationMobileForm
 * @ignore
 * @codeCoverageIgnore
 */
class AdditionalInformationMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = CustomProfile::denormalize($this->resource, [
            'for_form'     => true,
            'section_type' => CustomField::SECTION_TYPE_GROUP,
        ]);

        $this->title(__p("group::phrase.label.additional_information"))
            ->action("group/profile/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/group')
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'resolution'   => MetaFoxConstant::RESOLUTION_MOBILE,
        ]);
    }
}
