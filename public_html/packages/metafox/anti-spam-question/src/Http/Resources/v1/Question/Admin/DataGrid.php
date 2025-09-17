<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

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
    protected string $appName      = 'antispamquestion';
    protected string $resourceName = 'question';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->setDataSource('/admincp/antispamquestion/question', ['q' => ':q']);
        $this->sortable();

        $this->addColumn('image')
            ->header(__p('core::web.photo'))
            ->alignCenter()
            ->asPreviewUrl()
            ->width(150);

        $this->addColumn('question')
            ->header(__p('antispamquestion::phrase.question'))
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(200);

        $this->addColumn('is_case_sensitive')
            ->header(__p('antispamquestion::phrase.case_sensitive'))
            ->asToggle('caseSensitive')
            ->width(200);

        $this->addColumn('created_at')
            ->header(__p('core::phrase.created_at'))
            ->alignRight()
            ->asDateTime()
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['toggleActive', 'destroy']);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/antispamquestion/question/order');

            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.editItem');

            $actions->add('caseSensitive')
                ->apiUrl('admincp/antispamquestion/question/case-sensitive/:id');

            $actions->add('view_answer')
                ->apiUrl('admincp/core/form/antispamquestion.view_answer/:id')
                ->asFormDialog(true);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {});

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('view_answer')
                ->label(__p('antispamquestion::phrase.view_answers'))
                ->asEditRow()
                ->action('view_answer');
            $menu->withEdit();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('antispamquestion::phrase.create_question'))
                ->removeAttribute('value')
                ->to('antispamquestion/question/create');
        });
    }
}
