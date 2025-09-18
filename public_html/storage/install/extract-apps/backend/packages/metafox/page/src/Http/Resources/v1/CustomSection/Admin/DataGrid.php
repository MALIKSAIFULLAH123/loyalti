<?php

namespace MetaFox\Page\Http\Resources\v1\CustomSection\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\AbstractDataGrid as Grid;
use MetaFox\Profile\Support\CustomField;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected function getApiParams(): array
    {
        return [
            'section_type' => $this->getSectionType(),
        ];
    }

    public function getSectionType(): string
    {
        return CustomField::SECTION_TYPE_PAGE;
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('profile::phrase.add_custom_section'))
            ->disabled(false)
            ->to('page/section/create')
            ->params(['action' => 'addItem']);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        $actions->add('addItem')
            ->apiUrl(apiUrl('admin.page.section.create'));
    }
}
