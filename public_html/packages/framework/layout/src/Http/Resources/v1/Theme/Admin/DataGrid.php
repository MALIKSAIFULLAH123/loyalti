<?php

namespace MetaFox\Layout\Http\Resources\v1\Theme\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
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
    protected string $appName      = 'layout';
    protected string $resourceName = 'theme';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('preview')
            ->header(__p('core::phrase.thumbnail'))
            ->asPreviewUrl()
            ->alignCenter()
            ->width(200);

        $this->addColumn('total_variant')
            ->header(__p('layout::phrase.variants'))
            ->linkTo('links.viewVariant')
            ->width(80);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        $this->addColumn('created_at')
            ->header(__p('layout::phrase.created_at'))
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy', 'toggleActive']);

            $actions->add('createVariant')
                ->asFormDialog(false)
                ->link('links.createVariant');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            // $menu->addItem('createVariant')
            //     ->icon('ico-pencil-o')
            //     ->value(MetaFoxForm::ACTION_ROW_EDIT)
            //     ->label(__p('layout::phrase.create_variant'))
            //     ->params(['action' => 'createVariant'])
            //     ->showWhen(['falsy', true]);

            // $menu->withDelete(null, [], [
            //     'and',
            //     ['eq', 'item.is_active', 'false']
            // ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('layout::phrase.create_theme'))
                ->removeAttribute('value')
                ->showWhen(['eq', 'settings.app.env', 'local'])
                ->to('layout/theme/create');
        });
    }
}
