<?php

namespace MetaFox\Page\Http\Resources\v1\CustomField\Admin;

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
        return CustomField::SECTION_TYPE_PAGE;
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('profile::phrase.add_custom_field'))
            ->disabled(false)
            ->to('page/field/create')
            ->params(['action' => 'addItem']);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        $actions->add('addItem')
            ->apiUrl(apiUrl('admin.page.field.create'));
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
            ->pageUrl("/page/field/duplicate/:id")
            ->apiUrl(apiUrl('admin.page.field.duplicate', ['id' => ':id']));
    }
}
