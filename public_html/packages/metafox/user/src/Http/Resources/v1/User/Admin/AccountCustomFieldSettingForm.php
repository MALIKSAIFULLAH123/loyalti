<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class AccountCustomFieldSettingForm.
 * @property Model $resource
 * @driverType form
 * @driverName user.update.custom_field
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountCustomFieldSettingForm extends AbstractForm
{
    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = CustomProfile::denormalize($this->resource, [
            'for_form'     => true,
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]);

        $this->title(__p('core::web.custom_fields'))
            ->action('admincp/user/custom-field/' . $this->resource->id)
            ->asPatch()
            ->setValue($values);
    }

    public function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $this->addDefaultFooter(true);
    }
}
