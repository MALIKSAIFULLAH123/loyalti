<?php

namespace MetaFox\Authorization\Http\Resources\v1\Permission\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Authorization\Http\Requests\v1\Permission\Admin\EditFormRequest;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditForm.
 */
class EditPermissionForm extends AbstractForm
{
    /** @var Collection<int, Permission> */
    public Collection $permissions;

    public Role $role;

    public ?string $moduleId;

    public ?string $appName;

    /**
     * @throws AuthenticationException
     */
    public function boot(
        EditFormRequest $request,
        PermissionRepositoryInterface $permissionRepository,
        RoleRepositoryInterface $roleRepository
    ): void {
        $context                   = user();
        $params                    = array_merge($request->validated(), [
            'exclude_actions' => resolve(PermissionSettingRepositoryInterface::class)->getExcludedActions(),
        ]);

        $roleId = Arr::get($params, 'role_id') ?? 0;

        if (UserRole::SUPER_ADMIN_USER == $roleId) {
            abort(401, __p('phrase.permission_deny'));
        }

        $this->role                = $roleRepository->find($roleId);
        $this->moduleId            = Arr::get($params, 'module_id');
        $this->appName             = Arr::get($params, 'app');
        $this->permissions         = $permissionRepository->getPermissionsForEdit($context, $params);

        $this->booted();
    }

    protected function booted()
    {
    }

    protected function prepare(): void
    {
        $this->noHeader()
            ->title(__p('user::phrase.manage_permissions'))
            ->asPut()
            ->action("/admincp/authorization/permission/{$this->getRoleId()}");
    }

    protected function initialize(): void
    {
        if ($this->permissions->isEmpty()) {
            $this->buildNoResultSection();

            return;
        }

        // Temporarily hide permission of some apps which are not ready yet
        // @todo: Should remove this after all features are implemented
        if (!$this->isFeatureReady($this->getAppName(), $this->getModuleId())) {
            $this->buildNoResultSection();

            return;
        }

        $moduleName = $this->getModuleId() ?? $this->getAppName();

        $this->buildBasicSection($moduleName);

        $this->addDefaultFooter(true);
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->role->entityId();
    }

    /**
     * @return string|null
     */
    public function getModuleId(): ?string
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

    /**
     * @param  string|null $moduleName
     * @return void
     */
    protected function buildBasicSection(?string $moduleName): void
    {
        $basic = $this->addBasic()->separateBetweenFields();

        $values = [
            'module_id' => $moduleName,
        ];

        foreach ($this->permissions as $row) {
            // Getting value per permission
            $name = $row->name;

            $value = match ($row->data_type) {
                MetaFoxDataType::BOOLEAN => (int) $this->role->hasPermissionTo($row->name),
                MetaFoxDataType::INTEGER => (int) $this->role->getPermissionValue($row->name),
                default                  => 0,
            };

            Arr::set($values, $name, $value);

            //Generate default field base on type
            $label        = $row->getLabelPhrase();
            $description  = $row->getHelpPhrase();
            $fieldCreator = $row->extra['fieldCreator'] ?? null;
            $fieldType    = $fieldCreator ? 'custom' : $row->data_type;

            switch ($fieldType) {
                case MetaFoxDataType::BOOLEAN:
                    $basic->addFields(
                        Builder::switch($name)
                            ->label(__p($label))
                            ->marginDense()
                            ->description(__p($description)),
                    );
                    break;
                case MetaFoxDataType::NUMERIC:
                    $basic->addFields(
                        Builder::text($name)
                            ->preventScrolling()
                            ->required()
                            ->label(__p($label))
                            ->marginNormal()
                            ->description(__p($description))
                            ->yup(
                                Yup::number()
                                    ->required()
                                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                            ),
                    );
                    break;
                case MetaFoxDataType::INTEGER:
                    $basic->addFields(
                        Builder::text($name)
                            ->preventScrolling()
                            ->required()
                            ->label(__p($label))
                            ->marginNormal()
                            ->description(__p($description))
                            ->yup(
                                Yup::number()
                                    ->required()
                                    ->int()
                                    ->min(0)
                                    ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                            ),
                    );
                    break;
                case 'custom':
                    $basic->addFields(
                        app()->call($fieldCreator, ['name' => $name, 'description' => $description, 'label' => $label]),
                    );
                    break;
            }
        }

        $this->setValue($values);
    }

    protected function buildNoResultSection(): void
    {
        $this->addBasic()->addFields(
            Builder::typography('no_result')
                ->tagName('p')
                ->plainText(__p('user::phrase.no_permissions_found')),
        );
    }

    private function isFeatureReady(?string $appName, ?string $moduleId): bool
    {
        $hiddenApps = [];

        if (in_array($appName, $hiddenApps)) {
            return false;
        }

        if (in_array($moduleId, $hiddenApps)) {
            return false;
        }

        return true;
    }
}
