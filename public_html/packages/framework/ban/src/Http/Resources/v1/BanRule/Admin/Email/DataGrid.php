<?php

namespace MetaFox\Ban\Http\Resources\v1\BanRule\Admin\Email;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Ban\Supports\Constants;
use MetaFox\Ban\Http\Resources\v1\BanRule\Admin\DataGrid as BaseDataGrid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends BaseDataGrid
{
    protected array $apiParams = [
        'q'    => ':q',
        'type' => Constants::BAN_EMAIL_TYPE,
    ];

    protected function addFindValueColumn(): void
    {
        $this->addColumn('find_value')
            ->header(__p('core::phrase.email'))
            ->flex();
    }

    protected function addCreateMenu(GridActionMenu $menu): void
    {
        $menu->withCreate()
            ->label(__p('ban::phrase.add_new_email'))
            ->removeAttribute('value')
            ->to('ban/email/create');
    }
}
