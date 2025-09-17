<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
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
    protected string $appName      = 'newsletter';
    protected string $resourceName = 'newsletter';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('subject')
            ->header(__p('newsletter::phrase.subject'))
            ->flex();

        $this->addColumn('user_name')
            ->header(__p('user::phrase.user'))
            ->minWidth(300)
            ->linkTo('user_link')
            ->target('_blank')
            ->truncateLines()
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('newsletter::phrase.added'))
            ->asDateTime()
            ->flex();

        $this->addColumn('process_text')
            ->header(__p('newsletter::phrase.process'))
            ->minWidth(300)
            ->flex();

        $this->addColumn('status_text')
            ->header(__p('newsletter::phrase.status'))
            ->minWidth(300)
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'destroy']);

            $actions->add('editItem')
                ->apiUrl('admincp/core/form/newsletter.update/:id');

            $actions->add('viewItem')
                ->asFormDialog(true)
                ->apiUrl('admincp/core/form/newsletter.update/:id')
                ->apiParams([
                    'viewOnly' => true,
                ]);

            $actions->add('sendTestMail')
                ->asGet()
                ->apiUrl('admincp/core/form/newsletter.send_test_mail/:id');

            $actions->add('process')
                ->apiUrl('admincp/newsletter/process/:id')
                ->asPatch();

            $actions->add('reprocess')
                ->apiUrl('admincp/newsletter/reprocess/:id')
                ->asPatch();

            $actions->add('resend')
                ->apiUrl('admincp/newsletter/resend/:id')
                ->asPatch();

            $actions->add('stop')
                ->apiUrl('admincp/newsletter/stop/:id')
                ->asPatch();
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->withDelete();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('process')
                ->action('process')
                ->showWhen(['truthy', 'item.extra.can_process'])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('newsletter::phrase.process_newsletter'))
                ->reload();

            $menu->addItem('reprocess')
                ->action('reprocess')
                ->showWhen(['truthy', 'item.extra.can_reprocess'])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('newsletter::phrase.reprocess_newsletter'))
                ->reload();

            $menu->addItem('stop')
                ->action('stop')
                ->showWhen(['truthy', 'item.extra.can_stop'])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('newsletter::phrase.stop_newsletter'))
                ->reload();

            $menu->addItem('resend')
                ->action('resend')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->showWhen(['truthy', 'item.extra.can_resend'])
                ->label(__p('newsletter::phrase.resend_newsletter'))
                ->reload();

            $menu->addItem('sendTest')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('newsletter::phrase.send_test_mail'))
                ->params(['action' => 'sendTestMail']);

            $menu->withEdit()
                ->label(__p('newsletter::phrase.edit_newsletter'))
                ->showWhen(['truthy', 'item.extra.can_edit'])
                ->params(['action' => 'editItem']);

            $menu->addItem('view')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('newsletter::phrase.view_newsletter'))
                ->params([
                    'action' => 'viewItem',
                ]);

            $menu->withDelete()
                ->showWhen(['truthy', 'item.extra.can_delete'])
                ->label(__p('newsletter::phrase.delete_newsletter'));
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('newsletter::phrase.create_newsletter'))
                ->removeAttribute('value')
                ->to('newsletter/newsletter/create');
        });
    }
}
