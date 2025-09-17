<?php

namespace MetaFox\Group\Http\Resources\v1\CustomField\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\AbstractDataGrid as Grid;
use MetaFox\Profile\Support\CustomField;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    public function getSectionType(): string
    {
        return CustomField::SECTION_TYPE_GROUP;
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('profile::phrase.add_custom_field'))
            ->disabled(false)
            ->to('group/field/create')
            ->params(['action' => 'addItem']);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        $actions->add('addItem')
            ->apiUrl(apiUrl('admin.group.field.create'));
    }

    protected function getRolesColumn(): void
    {

    }

    protected function getIsRegisterColumn(): void
    {

    }

    protected function getActionDuplicate(Actions $actions): void
    {
        $actions->add('duplicate')
            ->asFormDialog(false)
            ->asGet()
            ->pageUrl("/group/field/duplicate/:id")
            ->apiUrl(apiUrl('admin.group.field.duplicate', ['id' => ':id']));
    }
}
