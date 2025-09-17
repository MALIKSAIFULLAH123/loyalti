<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\AbstractDataGrid as Grid;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    public function getSectionType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_USER;
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        if ($this->getSectionType() != CustomFieldSupport::SECTION_TYPE_USER) {
            return;
        }

        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('profile::phrase.add_custom_field'))
            ->disabled(false)
            ->to('profile/field/create')
            ->params(['action' => 'addItem']);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        $actions->add('addItem')
            ->apiUrl(apiUrl('admin.profile.field.create'));
    }

    protected function getRolesColumn(): void
    {
        $this->addColumn('roles')
            ->header(__p('core::phrase.applicable_roles'))
            ->flex();
    }

    protected function getIsRegisterColumn(): void
    {
        if ($this->getSectionType() != CustomFieldSupport::SECTION_TYPE_USER) {
            return;
        }

        $this->addColumn('is_register')
            ->header(__p('profile::phrase.is_register'))
            ->width(150)
            ->asToggle('register');
    }
}
