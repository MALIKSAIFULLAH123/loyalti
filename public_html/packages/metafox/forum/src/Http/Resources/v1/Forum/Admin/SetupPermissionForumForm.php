<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;


use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;

class SetupPermissionForumForm extends AbstractForm
{
    private int $id;

    protected function prepare(): void
    {
        $values = $this->getPermissionValues();

        $this->title(__p('forum::phrase.manage_permissions'))
            ->asPost()
            ->action('admincp/forum/forum/setup-permissions/:id')
            ->setValue($values);
    }

    public function boot(?int $id = null)
    {
        $this->id = $id;
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::choice('role_id')
                ->label(__p('forum::phrase.user_role'))
                ->required()
                ->options($this->getRoleOptions()),
        );

        foreach ($this->getRoleOptions() as $aRole) {
            foreach ($this->getPermissions() as $aPerm) {
                $basic->addField(
                    Builder::switch($aPerm['name'].'__'.$aRole['value'])
                        ->label($aPerm['label'])
                        ->showWhen(['eq', 'role_id', $aRole['value']])
                );
            }
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.submit')),
                Builder::cancelButton(),
            );
    }

    protected function getPermissionValues(): array
    {
        $aPermissionValues = [];
        $aPermissionOptions = $this->getPermissions();
        $aRoleOptions = $this->getRoleOptions();

        $aPermissionValuesByForumId = resolve(UserRolePermissionRepositoryInterface::class)->getAllPermissionByForumId($this->id);

        foreach ($aRoleOptions as $aRole) {
            foreach ($aPermissionOptions as $aPermission) {
                $aPermissionValues[$aPermission['name']. '__' .$aRole['value']] = true;

                foreach ($aPermissionValuesByForumId as $aPermissionValue) {
                    if ($aPermissionValue['role_id'] == $aRole['value'] && $aPermissionValue['permission_name'] == $aPermission['name']) {
                        $aPermissionValues[$aPermission['name']. '__' .$aRole['value']] = $aPermissionValue['permission_value'];
                    }
                }
            }
        }

        return $aPermissionValues;
    }

    protected function getRoleOptions(): array
    {
        //remove super admin role
        return resolve(RoleRepositoryInterface::class)->getRoleOptionsWithout([1]);
    }

    protected function getPermissions(): array
    {
        $aPermissionOptions = resolve(UserRolePermissionRepositoryInterface::class)->getPermissionOptions();
        $aPermissionOptions = array_map(function ($aPermission) {
            return [
                'name' => $aPermission['name'],
                'label' => $aPermission['phrase'],
            ];
        }, $aPermissionOptions);

        return $aPermissionOptions;
    }
}
