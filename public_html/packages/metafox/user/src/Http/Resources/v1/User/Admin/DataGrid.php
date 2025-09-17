<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

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
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\User\Models\UserVerify;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'user';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->setSearchForm(new SearchUserForm());
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->searchFormPlacement('header');

        $this->setDataSource(apiUrl('admin.user.index'), [
            'q'                => ':q',
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
            'currency_id'      => ':currency_id',
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
            ->asFeaturedUser()
            ->sortable()
            ->sortableField('full_name')
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
            ->width(200)
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('user::phrase.registration_date'))
            ->sortable()
            ->sortableField('created_at')
            ->asDateTime()
            ->flex();

        $this->addColumn('country_name')
            ->header(__p('core::country.country'))
            ->width(200)
            ->flex();

        $this->addColumn('last_activity')
            ->header(__p('user::phrase.last_activity'))
            ->sortable()
            ->sortableField('last_activity')
            ->asDateTime()
            ->flex();

        $this->addColumn('last_login')
            ->header(__p('user::phrase.last_login'))
            ->sortable()
            ->sortableField('last_login')
            ->asDateTime()
            ->flex();

        $this->addColumn('ip_address')
            ->header(__p('user::phrase.ip_address'))
            ->sortable()
            ->sortableField('ip_address')
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);

            $actions->addEditPageUrl();

            $this->actionApproveUser($actions);
            $this->actionDeniedUser($actions);
            $this->actionResendVerificationEmail($actions);
            $this->actionResendVerificationPhoneNumber($actions);
            $this->actionRemoveAuthentication($actions);
            $this->actionFeatureUser($actions);
            $this->actionUnFeatureUser($actions);
            $this->actionBanItem($actions);
            $this->actionUnBanItem($actions);
            $this->actionMoveRole($actions);
            $this->actionBatchApprove($actions);
            $this->actionBan($actions);
            $this->actionUnBan($actions);
            $this->actionDelete($actions);
            $this->actionBatchVerify($actions);
            $this->actionVerifyUser($actions);
            $this->actionBatchResendVerificationEmail($actions);
            $this->actionBatchResendVerificationPhoneNumber($actions);
            $this->getBatchExportActionMenu($actions);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->asButton();
            $this->batchApprove($menu);
            $this->batchVerify($menu);
            $this->batchResendVerificationEmail($menu);
            $this->batchResendVerificationPhoneNumber($menu);
            $this->batchMoveRole($menu);
            $this->getBatchExportActionMenu($menu);
            $this->batchBan($menu);
            $this->batchUnBan($menu);
            $this->batchDelete($menu);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()->showWhen(['truthy', 'item.extra.can_edit']);
            $menu->withDelete()->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['truthy', 'item.extra.can_delete'],
            ]);
            $this->banItem($menu);
            $this->unBanItem($menu);
            $this->featureUser($menu);
            $this->unFeatureUser($menu);
            $this->approveUser($menu);
            $this->deniedUser($menu);
            $this->removeAuthentication($menu);
            $this->resendVerificationEmail($menu);
            $this->resendVerificationPhoneNumber($menu);
            $this->verifyUserMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_members',
        ]);
    }

    protected function actionVerifyUser(Actions $actions): void
    {
        $actions->add('verifyUser')
            ->apiUrl('admincp/user/verify-user/:id')
            ->asPatch();
    }

    protected function actionApproveUser(Actions $actions): void
    {
        $actions->add('approveUser')
            ->apiUrl('admincp/user/approve/:id')
            ->asPatch();
    }

    protected function actionDeniedUser(Actions $actions): void
    {
        $actions->add('deniedUser')
            ->apiUrl('admincp/core/form/user.deny_user/:id')
            ->asGet();
    }

    protected function actionResendVerificationEmail(Actions $actions): void
    {
        $actions->add('resendVerificationEmail')
            ->apiUrl('admincp/user/resend-verification/:id')
            ->apiParams(['action' => UserVerify::ACTION_EMAIL])
            ->asPatch();
    }

    protected function actionResendVerificationPhoneNumber(Actions $actions): void
    {
        $actions->add('resendVerificationPhoneNumber')
            ->apiUrl('admincp/user/resend-verification/:id')
            ->apiParams(['action' => UserVerify::ACTION_PHONE_NUMBER])
            ->asPatch();
    }

    protected function actionRemoveAuthentication(Actions $actions): void
    {
        $actions->add('removeAuthentication')
            ->asGet()
            ->apiUrl('admincp/core/form/mfa.user_service.remove_authentication/:id');
    }

    protected function actionFeatureUser(Actions $actions): void
    {
        $actions->add('featureUser')
            ->apiUrl('admincp/user/feature/:id')
            ->apiParams(['feature' => 1])
            ->asPatch();
    }

    protected function actionUnFeatureUser(Actions $actions): void
    {
        $actions->add('unFeatureUser')
            ->apiUrl('admincp/user/feature/:id')
            ->apiParams(['feature' => 0])
            ->asPatch();
    }

    protected function actionBanItem(Actions $actions): void
    {
        $actions->add('banItem')
            ->asGet()
            ->apiUrl('admincp/core/form/user.ban/:id');
    }

    protected function actionUnBanItem(Actions $actions): void
    {
        $actions->add('unBanItem')
            ->apiUrl('admincp/user/ban/:id')
            ->asFormDialog(false)
            ->asDelete();
    }

    protected function actionMoveRole(Actions $actions): void
    {
        $actions->add('batchMoveRole')
            ->asGet()
            ->apiUrl('admincp/core/form/user.batch_move_role?user_ids=[:id]');
    }

    protected function actionBatchApprove(Actions $actions): void
    {
        $actions->add('batchApprove')
            ->asPatch()
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-approve?id=[:id]');
    }

    protected function actionBan(Actions $actions): void
    {
        $actions->add('batchBan')
            ->asPost()
            ->asFormDialog(false)
            ->apiParams(['id' => ':id'])
            ->apiUrl('admincp/user/batch-ban')
            ->confirm(['message' => __p('user::phrase.are_you_sure_you_want_to_ban_selected_users')]);
    }

    protected function actionUnBan(Actions $actions): void
    {
        $actions->add('batchUnBan')
            ->asDelete()
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-ban?id=[:id]')
            ->confirm(['message' => __p('user::phrase.are_you_sure_you_want_to_un_ban_selected_users')]);
    }

    protected function actionDelete(Actions $actions): void
    {
        $actions->add('batchDelete')
            ->asDelete()
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-delete?id=[:id]')
            ->confirm(['message' => __p('user::phrase.skip_users_equal_or_higher'),]);
    }

    protected function actionBatchVerify(Actions $actions): void
    {
        $actions->add('batchVerify')
            ->asPatch()
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-verify?id=[:id]');
    }

    protected function actionBatchResendVerificationEmail(Actions $actions): void
    {
        $actions->add('batchResendVerificationEmail')
            ->asPost()
            ->apiParams(['id' => ':id', 'action' => UserVerify::ACTION_EMAIL])
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-resend-verification');
    }

    protected function actionBatchResendVerificationPhoneNumber(Actions $actions): void
    {
        $actions->add('batchResendVerificationPhoneNumber')
            ->asPost()
            ->apiParams(['id' => ':id', 'action' => UserVerify::ACTION_PHONE_NUMBER])
            ->asFormDialog(false)
            ->apiUrl('admincp/user/batch-resend-verification');
    }

    protected function batchMoveRole(BatchActionMenu $menu): void
    {
        $menu->addItem('batchMoveRole')
            ->action('batchMoveRole')
            ->icon('ico-reply-o')
            ->label(__p('user::phrase.move_to_role'))
            ->reload()
            ->asBatchEdit();
    }

    protected function getBatchExportActionMenu(BatchActionMenu|Actions $menu): void
    {
        if (!user()->hasPermissionTo('admincp.has_system_access')) {
            return;
        }

        if ($menu instanceof Actions) {
            $menu->add('actionBatchExport')
                ->asGet()
                ->apiUrl('admincp/core/form/user.export_process.create?ids=[:id]');
        }

        if ($menu instanceof BatchActionMenu) {
            $menu->addItem('actionBatchExport')
                ->action('actionBatchExport')
                ->icon('ico-download')
                ->label(__p('user::phrase.export_users'))
                ->reload()
                ->asBatchEdit();
        }
    }

    protected function batchApprove(BatchActionMenu $menu): void
    {
        $menu->addItem('batchApprove')
            ->action('batchApprove')
            ->icon('ico-check-circle-o')
            ->label(__p('core::phrase.approve'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchBan(BatchActionMenu $menu): void
    {
        $menu->addItem('batchBan')
            ->action('batchBan')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.ban'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchUnBan(BatchActionMenu $menu): void
    {
        $menu->addItem('batchUnBan')
            ->action('batchUnBan')
            ->icon('ico-unlock-o')
            ->label(__p('user::phrase.unban'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchDelete(BatchActionMenu $menu): void
    {
        $menu->addItem('batchDelete')
            ->action('batchDelete')
            ->icon('ico-trash-o')
            ->label(__p('user::phrase.delete'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchVerify(BatchActionMenu $menu): void
    {
        $menu->addItem('batchVerify')
            ->action('batchVerify')
            ->icon('ico-check-circle-o')
            ->label(__p('user::phrase.verify'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchResendVerificationEmail(BatchActionMenu $menu): void
    {
        $menu->addItem('batchResendVerificationEmail')
            ->action('batchResendVerificationEmail')
            ->icon('ico-envelope-o')
            ->label(__p('user::phrase.resend_verification_email'))
            ->reload()
            ->asBatchEdit();
    }

    protected function batchResendVerificationPhoneNumber(BatchActionMenu $menu): void
    {
        $menu->addItem('batchResendVerificationPhoneNumber')
            ->action('batchResendVerificationPhoneNumber')
            ->icon('ico-comment-square-o')
            ->label(__p('user::phrase.resend_verification_sms'))
            ->reload()
            ->asBatchEdit();
    }

    protected function banItem(ItemActionMenu $menu): void
    {
        $menu->addItem('banItem')
            ->action('banItem')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.ban_user'))
            ->reload()
            ->asEditRow()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_ban'],
                ['falsy', 'item.is_banned'],
            ]);
    }

    protected function unBanItem(ItemActionMenu $menu): void
    {
        $menu->addItem('unBanItem')
            ->action('unBanItem')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.unban_user'))
            ->reload()
            ->asEditRow()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_ban'],
                ['truthy', 'item.is_banned'],
            ]);
    }

    protected function featureUser(ItemActionMenu $menu): void
    {
        $menu->addItem('featureUser')
            ->action('featureUser')
            ->icon('ico-diamond')
            ->label(__p('user::phrase.feature_user'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_feature'],
                ['falsy', 'item.is_featured'],
            ]);
    }

    protected function unFeatureUser(ItemActionMenu $menu): void
    {
        $menu->addItem('unFeatureUser')
            ->action('unFeatureUser')
            ->icon('ico-diamond')
            ->label(__p('user::phrase.unfeature_user'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_feature'],
                ['truthy', 'item.is_featured'],
            ]);
    }

    protected function approveUser(ItemActionMenu $menu): void
    {
        $menu->addItem('approveUser')
            ->action('approveUser')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.approve_user'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['falsy', 'item.is_approved'],
            ]);
    }

    protected function deniedUser(ItemActionMenu $menu): void
    {
        $menu->addItem('deniedUser')
            ->action('deniedUser')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.deny_user'))
            ->value(MetaFoxForm::ACTION_ROW_EDIT)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['neq', 'item.approve_status', MetaFoxConstant::STATUS_NOT_APPROVED],
                ['falsy', 'item.is_approved'],
            ]);
    }

    protected function removeAuthentication(ItemActionMenu $menu): void
    {
        $menu->addItem('removeAuthentication')
            ->action('removeAuthentication')
            ->icon('ico-lock-o')
            ->label(__p('user::phrase.remove_authentication'))
            ->reload()
            ->asEditRow()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['truthy', 'item.is_mfa_enabled'],
            ]);
    }

    protected function resendVerificationEmail(ItemActionMenu $menu): void
    {
        $menu->addItem('resendVerificationEmail')
            ->action('resendVerificationEmail')
            ->icon('ico-envelope-o')
            ->label(__p('user::phrase.resend_verification_email'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['truthy', 'item.extra.can_resend_email'],
            ]);
    }

    protected function resendVerificationPhoneNumber(ItemActionMenu $menu): void
    {
        $menu->addItem('resendVerificationPhoneNumber')
            ->action('resendVerificationPhoneNumber')
            ->icon('ico-comment-square-o')
            ->label(__p('user::phrase.resend_verification_sms'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['truthy', 'item.extra.can_resend_phone_number'],
            ]);
    }

    protected function verifyUserMenu(ItemActionMenu $menu): void
    {
        $menu->addItem('verifyUser')
            ->action('verifyUser')
            ->icon('ico-check-circle-o')
            ->label(__p('user::phrase.verify'))
            ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
            ->reload()
            ->showWhen([
                'and',
                ['truthy', 'item.extra.can_edit'],
                ['truthy', 'item.extra.can_verify'],
            ]);
    }
}
