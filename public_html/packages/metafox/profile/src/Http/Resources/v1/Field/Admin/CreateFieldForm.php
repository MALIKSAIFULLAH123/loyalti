<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Models\Field as Model;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Traits\CreateFieldFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateFieldForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateFieldForm extends AbstractForm
{
    use CreateFieldFormTrait;

    public function getActiveField(): ?AbstractField
    {
        return Builder::checkbox('is_active')
            ->label(__p('core::phrase.is_active'));
    }

    public function getRegisterField(): ?AbstractField
    {
        return Builder::checkbox('is_register')
            ->label(__p('profile::phrase.is_register'));
    }

    public function getRoleField(): ?AbstractField
    {
        return Builder::choice('roles')
            ->multiple(true)
            ->label(__p('profile::phrase.applicable_roles'))
            ->description(__p('profile::phrase.define_which_user_roles_can_include_this_field'))
            ->options(CustomFieldFacade::getAllowedRoleOptions());
    }

    public function getVisibleRoleField(): ?AbstractField
    {
        return Builder::choice('visible_roles')
            ->label(__p('profile::phrase.visible_for_user_roles'))
            ->description(__p('profile::phrase.determine_which_user_roles_can_view_this_field'))
            ->multiple(true)
            ->options(CustomFieldFacade::getAllowedVisibleRoleOptions());
    }

    public function isEdit(): bool
    {
        return false;
    }
}
