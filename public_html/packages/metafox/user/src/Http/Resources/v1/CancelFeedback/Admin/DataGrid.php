<?php

namespace MetaFox\User\Http\Resources\v1\CancelFeedback\Admin;

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
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'cancel-feedback';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchCancelFeedbackForm());

        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('user::phrase.display_name'))
            ->flex();

        $this->addColumn('email')
            ->header(__p('core::phrase.email'))
            ->minWidth(300)
            ->flex();

        $this->addColumn('phone_number')
            ->header(__p('core::phrase.phone_number'))
            ->minWidth(200)
            ->flex();

        $this->addColumn('role_name')
            ->header(__p('core::phrase.role'))
            ->minWidth(300)
            ->flex();

        $this->addColumn('reason_text')
            ->header(__p('user::phrase.reason'))
            ->minWidth(300)
            ->flex();

        $this->addColumn('feedback_text')
            ->header(__p('user::phrase.user_feedback'))
            ->minWidth(300)
            ->truncateLines()
            ->flex();

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.deleted_on_label'))
            ->asDateTime()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
            $actions->add('viewFeedback')
                ->apiUrl('admincp/core/form/user.cancelled.view_feedback/:id');
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
            $menu->addItem('viewFeedback')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('user::phrase.view_cancel_feedback'))
                ->showWhen([
                    'or',
                    ['neq', 'item.feedback_text', ''],
                ])
                ->params([
                    'action'      => 'viewFeedback',
                    'dialogProps' => [
                        'fullWidth' => true,
                    ],
                ])
                ->as('menuItem.dataGird.as.labelIcu');

            $menu->withDelete();
        });
    }
}
