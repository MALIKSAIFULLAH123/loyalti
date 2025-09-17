<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Support\Facade\CustomField;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 * @driverType data-grid
 * @driverName user.role
 */
class DataGrid extends Grid
{
    protected string $appName      = 'authorization';
    protected string $resourceName = 'role';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchRoleForm());

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('url')
            ->flex();

        $this->addColumn('is_custom')
            ->header(__p('core::phrase.is_custom'))
            ->asYesNoIcon();

        $this->addColumn('total_users')
            ->header(__p('user::phrase.total_users'))
            ->linkTo('browse_users_url')
            ->asNumber()
            ->width(150);

        $this->addColumn('total_inherited')
            ->header(__p('authorization::phrase.total_inherited'))
            ->asNumber()
            ->width(150);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy']);

            $actions->add('getDeleteForm')
                ->apiUrl('admincp/core/form/user.role.delete/:id');
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('authorization::phrase.add_new_role'))
                ->removeAttribute('value')
                ->to('authorization/role/create');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('managePermissions')
                ->label(__p('user::phrase.manage_permissions'))
                ->value(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
                ->params([
                    'url' => '/admincp/authorization/permission?role_id=:id',
                ])
                ->showWhen(['neq', 'item.id', UserRole::SUPER_ADMIN_USER_ID]);

            $menu->addItem('manageCustomFields')
                ->label(__p('profile::phrase.manage_custom_fields'))
                ->value(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
                ->params([
                    'url' => '/admincp/profile/field/browse?section_type=' . CustomFieldSupport::SECTION_TYPE_USER . '&role_id=:id',
                ])->showWhen(['includes', 'item.id', CustomField::getAllowedRole()]);

            $menu->withEdit();

            $menu->addItem('deleteItem')
                ->icon('ico-trash-o')
                ->value(MetaFoxForm::ACTION_ROW_ADD)
                ->label(__p('core::phrase.delete'))
                ->params([
                    'action' => 'getDeleteForm',
                ])
                ->showWhen([
                    'truthy',
                    'item.is_custom',
                ]);
        });
    }
}
