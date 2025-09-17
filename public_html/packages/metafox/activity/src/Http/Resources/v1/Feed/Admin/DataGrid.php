<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed\Admin;

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
    protected string $appName = 'feed';

    protected string $resourceName = 'feed';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchFeedForm());

        $this->setDefaultDataSource();
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [20, 50, 100]);

        $this->addColumn('user.display_name')
            ->header(__p('activity::phrase.posted_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->width(160);

        $this->addColumn('owner.display_name')
            ->header(__p('activity::phrase.posted_to'))
            ->linkTo('owner.url')
            ->target('_blank')
            ->width(160);

        $this->addColumn('headline')
            ->header(__p('activity::phrase.headline'))
            ->renderAs('FeedHeadlineInfoCell')
            ->setAttribute('headlineInfoMappingField', 'embed_object')
            ->truncateLines(4)
            ->flex();

        $this->addColumn('content')
            ->header(__p('core::phrase.content_label'))
            ->truncateLines(4)
            ->flex();

        $this->addColumn('item_type_label')
            ->header(__p('activity::phrase.item'))
            ->linkTo('url')
            ->target('_blank')
            ->width(170);

        $this->addColumn('feed_type')
            ->header(__p('activity::phrase.feed_type'))
            ->width(250);

        $this->addColumn('created_at')
            ->header(__p('core::phrase.date'))
            ->asDateTime()
            ->width(160);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'destroy']);

            $actions->add('deleteWithItems')
                ->asDelete()
                ->apiUrl('admincp/feed/feed/items/:id');
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('view')
                ->label(__p('core::phrase.view'))
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->params([
                    'to'     => url_utility()->makeApiFullUrl('/feed/:id'),
                    'target' => '_blank',
                ]);

            $menu->withDelete()
                ->label(__p('activity::phrase.delete_post'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_delete'],
                ]);

            $menu->addItem('deleteWithItems')
                ->action('deleteWithItems')
                ->label(__p('activity::phrase.delete_post_with_items'))
                ->value(MetaFoxForm::ACTION_ROW_DELETE)
                ->confirm(['message' => __p('activity::phrase.delete_with_items_confirm')])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_delete_with_items'],
                ]);
        });
    }
}
