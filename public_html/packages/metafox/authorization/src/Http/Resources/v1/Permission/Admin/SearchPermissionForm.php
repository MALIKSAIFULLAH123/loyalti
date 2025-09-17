<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Authorization\Http\Resources\v1\Permission\Admin;

use Illuminate\Http\Request;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * class SearchPermissionForm.
 *
 * @driverType form
 * @driverName user_permission.search
 */
class SearchPermissionForm extends AbstractForm
{
    private ?string $moduleId;

    private int $roleId;

    private ?string $appName;

    public function boot(Request $request): void
    {
        $this->moduleId = $request->get('module_id');
        $this->roleId   = (int) $request->get('role_id', 4);
        $this->appName  = $request->get('app');
    }

    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/authorization/permission')
            ->submitAction(MetaFoxForm::FORM_ADMIN_SUBMIT_ACTION_SEARCH)
            ->setAttribute('submitUrl', "/{$this->getAppName()}/permission")
            ->submitOnValueChanged(true)
            ->setValue([
                'module_id' => $this->getModuleId() ?? $this->getAppName(),
                'role_id'   => $this->roleId,
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getRoleOptions(): array
    {
        $return = [];

        $roles  = resolve(RoleRepositoryInterface::class)->getUsableRoles();

        foreach ($roles as $role) {
            if (!$role instanceof Role) {
                continue;
            }

            if ($role->entityId() == 1) {
                continue;
            }

            $return[] = ['label' => $role->name, 'value' => $role->id];
        }

        return $return;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getModuleOptions(): array
    {
        $return = [];

        $modules = resolve('core.packages')->all();

        foreach ($modules as $module) {
            $return[] = ['value' => $module->alias, 'label' => $module->title];
        }

        return $return;
    }

    protected function initialize(): void
    {
        $basic = $this->addSection([
            'name' => 'search',
        ])->asHorizontal()->marginDense();

        if ($this->allowSearchByModule($this->getAppName() ?? '')) {
            $basic->addField($this->buildModuleOptionsField());
        }

        $basic->addFields(
            Builder::choice('role_id')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.role'))
                ->disableClearable()
                ->options($this->getRoleOptions())
        );
    }

    /**
     * @return FormField
     */
    protected function buildModuleOptionsField(): FormField
    {
        $groupOptions = array_merge(
            [['value' => '', 'label' => __p('core::phrase.all')]],
            resolve('core.packages')->getPackageHasPermissionOptions()
        );

        return Builder::choice('module_id')
            ->fullWidth(false)
            ->marginDense()
            ->label(__p('core::phrase.package_name'))
            ->disableClearable()
            ->options($groupOptions)
            ->withSmallSize();
    }

    /**
     * @param  string $moduleId
     * @return bool
     */
    protected function allowSearchByModule(string $moduleId): bool
    {
        return in_array($moduleId, ['authorization', 'user']);
    }

    /**
     * @return string|null
     */
    protected function getModuleId(): ?string
    {
        return $this->moduleId;
    }

    /**
     * @return string|null
     */
    public function getAppName(): ?string
    {
        return $this->appName;
    }
}
