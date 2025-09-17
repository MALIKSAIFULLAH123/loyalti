<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin;

use MetaFox\ActivityPoint\Support\Facade\PointSetting;
use MetaFox\ActivityPoint\Models\PointSetting as Model;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPointSettingForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName activitypoint_setting.search.admin
 */
class SearchPointSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.manage_point_settings'))
            ->action('/activitypoint/setting')
            ->setValue([
                'role_id' => UserRole::NORMAL_USER_ID,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();
        $basic->addFields(
            Builder::selectPackageAlias('module_id')
                ->forAdminSearchForm()
                ->options($this->getModuleOptions()),
            Builder::choice('role_id')
                ->forAdminSearchForm()
                ->label(__p('authorization::phrase.role'))
                ->disableClearable()
                ->options(PointSetting::getAllowedRoleOptions()),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function getModuleOptions(): array
    {
        $data = [
            [
                'label' => __p('core::phrase.all'),
                'value' => 'all',
            ],
        ];

        return array_merge($data, resolve(PointSettingRepositoryInterface::class)->getModuleOptions());
    }
}
