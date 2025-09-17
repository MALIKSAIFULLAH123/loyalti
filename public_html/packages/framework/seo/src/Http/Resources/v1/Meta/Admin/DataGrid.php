<?php

namespace MetaFox\SEO\Http\Resources\v1\Meta\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'seo';
    protected string $resourceName = 'metum';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchMetaForm());

        $this->setDataSource(apiUrl('admin.seo.metum.index'), [
            'q'          => ':q',
            'package_id' => ':package_id',
            'resolution' => ':resolution',
        ]);

        $this->addColumn('url')
            ->header(__p('core::phrase.url'))
            ->flex();

        $this->addColumn('package_name')
            ->header(__p('seo::phrase.package_name'))
            ->flex();

        $this->addColumn('resolution')
            ->header(__p('core::phrase.resolution'))
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);
            $actions->add('editSchema')
                ->apiUrl('/admincp/core/form/seo.meta.update_schema/:id');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->addItem('editSchema')
                ->icon('ico-pencil-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('seo::phrase.edit_schema'))
                ->params(['action' => 'editSchema']);
        });
    }
}
