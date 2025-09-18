<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

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
    protected string $appName      = 'sticker';
    protected string $resourceName = 'sticker-set';

    protected function initialize(): void
    {
        $this->inlineSearch(['title']);

        $this->setSearchForm(new SearchStickerSetForm());

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('thumbnail')
            ->header(__p('core::phrase.thumbnail'))
            ->renderAs('AvatarCell')
            ->width(200);

        $this->addColumn('total_sticker')
            ->header(__p('sticker::phrase.total_sticker'))
            ->width(150);

        $this->addColumn('view_only')
            ->header(__p('core::phrase.is_system'))
            ->asYesNoIcon()
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive']);
            $actions->addEditPageUrl();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()->showWhen(['falsy', 'item.is_default']);
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('sticker::phrase.add_new_sticker_set'))
                ->removeAttribute('value')
                ->to('sticker/sticker-set/create');
        });
    }
}
