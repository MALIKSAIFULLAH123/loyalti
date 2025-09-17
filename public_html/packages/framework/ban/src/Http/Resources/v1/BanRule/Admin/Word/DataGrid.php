<?php

namespace MetaFox\Ban\Http\Resources\v1\BanRule\Admin\Word;

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
        'type' => Constants::BAN_WORD_TYPE,
    ];

    protected function addFindValueColumn(): void
    {
        $this->addColumn('find_value')
            ->header(__p('ban::phrase.word'))
            ->flex();
    }

    protected function addOtherColumns(): void
    {
        $this->addColumn('replacement')
            ->header(__p('ban::phrase.replacement'))
            ->flex();
    }

    protected function addCreateMenu(GridActionMenu $menu): void
    {
        $menu->withCreate()
            ->label(__p('ban::phrase.add_new_word'))
            ->removeAttribute('value')
            ->to('ban/word/create');
    }
}
