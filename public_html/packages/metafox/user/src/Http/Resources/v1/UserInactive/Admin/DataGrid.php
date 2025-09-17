<?php

namespace MetaFox\User\Http\Resources\v1\UserInactive\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'inactive';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.user.inactive.index') . '?status=' . MetaFoxConstant::STATUS_APPROVED, [
            'q'                => ':q',
            'day'              => ':day',
            'email'            => ':email',
            'phone_number'     => ':phone_number',
            'group'            => ':group',
            'status'           => ':status',
            'gender'           => ':gender',
            'postal_code'      => ':postal_code',
            'country_state_id' => ':country_state_id',
            'country'          => ':country',
            'age_from'         => ':age_from',
            'age_to'           => ':age_to',
            'sort'             => ':sort',
            'ip_address'       => ':ip_address',
        ]);

        $this->enableCheckboxSelection();

        $this->addColumn('user')
            ->header(__p('core::web.photo'))
            ->renderAs('AvatarCell')
            ->width(120);

        $this->addColumn('display_name')
            ->header(__p('user::phrase.display_name'))
            ->linkTo('user_link')
            ->target('_blank')
            ->sortable()
            ->sortableField(SortScope::SORT_FULL_NAME)
            ->asFeaturedUser()
            ->width(200);

        $this->addColumn('email')
            ->header(__p('core::phrase.email_address'))
            ->asEmail('email')
            ->flex();

        $this->addColumn('phone_number')
            ->header(__p('core::phrase.phone_number'))
            ->flex();

        $this->addColumn('role_name')
            ->header(__p('core::phrase.role'))
            ->sortable()
            ->sortableField(SortScope::SORT_GROUP)
            ->width(200)
            ->flex();

        $this->addColumn('last_activity')
            ->header(__p('user::phrase.last_activity'))
            ->sortable()
            ->sortableField(SortScope::SORT_LAST_ACTIVITY)
            ->asDateTime()
            ->flex();

        $this->addColumn('last_login')
            ->header(__p('user::phrase.last_login'))
            ->sortable()
            ->sortableField(SortScope::SORT_LAST_LOGIN)
            ->asDateTime()
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('core::web.joined'))
            ->sortable()
            ->sortableField(SortScope::SORT_CREATED_AT)
            ->asDateTime()
            ->flex();

        $this->addColumn('ip_address')
            ->header(__p('user::phrase.ip_address'))
            ->sortable()
            ->sortableField(SortScope::SORT_IP_ADDRESS)
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('batchProcessMailing')
                ->asPost()
                ->asFormDialog(false)
                ->apiUrl(apiUrl('admin.user.inactive-process.batch-process'));

            $actions->add('processMailing')
                ->apiUrl('admincp/user/inactive-process/:id')
                ->asPatch();
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->asButton();

            $menu->addItem('batchProcessMailing')
                ->action('batchProcessMailing')
                ->label(__p('user::phrase.remind_via_email'))
                ->reload()
                ->asBatchEdit();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('processMailing')
                ->action('processMailing')
                ->label(__p('user::phrase.remind_via_email'))
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->reload();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {

            $menu->addItem('processMailingGrid')
                ->icon('ico-plus')
                ->label(__p('user::phrase.manage_mailing_processes'))
                ->disabled(false)
                ->to('user/inactive-process/browse');

        });
    }
}
