<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'tourguide';
    protected string $resourceName = 'tour-guide';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->setSearchForm(new SearchTourGuideForm());
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('tourguide::phrase.guide'))
            ->flex(3);

        $this->addColumn('url')
            ->header(__p('tourguide::phrase.page_url'))
            ->target('_blank')
            ->linkTo('url')
            ->flex(4);

        $this->addColumn('user.display_name')
            ->header(__p('tourguide::phrase.created_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->alignCenter()
            ->width(200);

        $this->addColumn('created_at')
            ->header(__p('tourguide::phrase.create_on'))
            ->asDateTime()
            ->width(300);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(200);

        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);

            $actions->add('reset')
                ->asPatch()
                ->apiUrl('admincp/tourguide/tour-guide/:id/reset');

            $actions->add('batchDelete')
                ->asDelete()
                ->asFormDialog(false)
                ->apiUrl('admincp/tourguide/tour-guide/batch-delete?id=[:id]')
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('tourguide::phrase.tour_guide_delete_confirm'),
                ]);
        });

        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->addItem('batchDelete')
                ->action('batchDelete')
                ->icon('ico-trash-o')
                ->label(__p('core::phrase.delete'))
                ->reload()
                ->asBatchEdit();
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();

            $menu->addItem('viewSteps')
                ->label(__p('tourguide::phrase.view_steps'))
                ->icon('ico-barchart-o')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->params([
                    'to' => '/tourguide/tour-guide/:id/step/browse',
                ]);

            $menu->addItem('reset')
                ->action('reset')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.reset'))
                ->reload();

            $menu->withDelete();
        });
    }
}
