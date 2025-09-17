<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Attachment\Http\Resources\v1\FileType\Admin\SearchFileTypeForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'attachment';
    protected string $resourceName = 'type';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchFileTypeForm());

        $this->addColumn('extension')
            ->header(__p('attachment::phrase.file_extension'))
            ->alignLeft()
            ->flex();

        $this->addColumn('mime_type')
            ->header(__p('attachment::phrase.file_mime_type'))
            ->alignLeft()
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'update', 'destroy', 'toggleActive']);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('attachment::phrase.add_new_type'))
                ->removeAttribute('value')
                ->to('attachment/type/add');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete(null, null, [
                'and',
                ['truthy', 'item.extra.can_delete'],
            ]);
        });
    }
}
