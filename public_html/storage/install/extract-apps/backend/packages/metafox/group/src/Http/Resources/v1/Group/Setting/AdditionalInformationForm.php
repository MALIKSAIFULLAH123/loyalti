<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
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
 * Class AdditionalInformationForm
 * @ignore
 * @codeCoverageIgnore
 */
class AdditionalInformationForm extends AbstractForm
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

        $this->action("group/profile/{$this->resource->entityId()}")
            ->secondAction('group/updateGroupInfo')
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
