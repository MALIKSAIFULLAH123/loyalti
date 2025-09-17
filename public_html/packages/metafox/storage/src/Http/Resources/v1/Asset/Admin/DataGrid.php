<?php

namespace MetaFox\Storage\Http\Resources\v1\Asset\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'storage';
    protected string $resourceName = 'asset';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchAssetForm());
        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('module_name')
            ->header(__p('core::phrase.package_name'))
            ->flex();

        $this->addColumn('preview_data')
            ->header(__p('core::phrase.preview'))
            ->asPreviewUrl()
            ->aspectRatio()
            ->width(200)
            ->alignCenter();

        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);
            $actions->add('resetDefault')
                ->apiMethod('GET')
                ->apiUrl(apiUrl('admin.storage.asset.revert.form', ['asset' => ':id']));
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();

            $menu->addItem('revertToDefault')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('core::phrase.reset_to_default'))
                ->params(['action' => 'resetDefault'])
                ->showWhen([
                    'and',
                    ['truthy', 'item.is_modified'],
                ]);
        });
    }
}
