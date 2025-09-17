<?php

namespace MetaFox\Music\Http\Resources\v1\Genre\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'music';
    protected string $resourceName = 'genre';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('statistic.total_item')
            ->header(__p('music::phrase.total_music'))
            ->linkTo('url')
            ->target('_blank')
            ->alignCenter()
            ->asNumber()
            ->width(150);
    }

    protected function getToTalSubColumn(): void
    {
        $this->addColumn('statistic.total_sub')
            ->header(__p('music::phrase.sub_genres'))
            ->linkTo('total_sub_link')
            ->asNumber()
            ->width(150)
            ->alignCenter();
    }

    protected function categoryRepository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }

    protected function defaultAction(Actions $actions): void
    {
        $actions->add('toggleDefault')
            ->apiUrl('admincp/music/genre/:id/default')
            ->asPatch();
    }

    protected function getParentColumn(): void
    {
        $this->addColumn('parent.name')
            ->header(__p('music::phrase.parent_genre'))
            ->truncateLines()
            ->flex();
    }

    protected function defaultMenu(ItemActionMenu $menu): void
    {
        $menu->addItem('default')
            ->value(MetaFoxForm::ACTION_ROW_ACTIVE)
            ->params(['action' => 'toggleDefault'])
            ->reload(true)
            ->showWhen([
                'and',
                ['falsy', 'item.is_default'],
                ['neq', 'item.is_active', 0],
            ])
            ->label(__p('core::phrase.mark_as_default'));
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('core::phrase.add_genre'))
            ->disabled(false)
            ->to(sprintf('%s/%s/%s', $this->appName, $this->resourceName, 'create'))
            ->params(['action' => 'addItem']);
    }
}
