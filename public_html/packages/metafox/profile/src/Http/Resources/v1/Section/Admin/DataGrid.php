<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\AbstractDataGrid as Grid;
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
            ->label(__p('profile::phrase.add_custom_section'))
            ->disabled(false)
            ->to('profile/section/create')
            ->params(['action' => 'addItem']);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        if ($this->getSectionType() != CustomFieldSupport::SECTION_TYPE_USER) {
            return;
        }

        $actions->add('addItem')
            ->apiUrl(apiUrl('admin.profile.section.create'));
    }
}
