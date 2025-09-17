<?php

namespace MetaFox\Report\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'report';
        $vars   = [
            'report.user_receive_notifications',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('report.user_receive_notifications')
                ->multiple()
                ->label(__p('report::phrase.user_receive_notifications_whenever_new_report'))
                ->options($this->getAdminAndStaffOptions())
        );

        $this->addDefaultFooter(true);
    }

    private function getAdminAndStaffOptions(): array
    {
        $listAdminStaff = $this->userRepository()->getAdminAndStaffOptions();

        if (empty($listAdminStaff)) {
            return [
                ['label' => __p('core::phrase.none'), 'value' => 0],
            ];
        }

        return $listAdminStaff;
    }

    protected function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }
}
