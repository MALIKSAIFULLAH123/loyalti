<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Repositories\Contracts\AccountSettingRepositoryInterface;
use stdClass;

/**
 * Class AccountSettingRepository.
 *
 * @property UserModel $model
 * @method   UserModel getModel()
 * @method   UserModel find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountSettingRepository extends AbstractRepository implements AccountSettingRepositoryInterface
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return UserModel::class;
    }

    public function getAccountSettings(User $user): array
    {
        $fields = $this->getAccountSettingFields($user);

        return $this->handleFields($user, $fields);
    }

    protected function getAccountSettingFields(User $user): array
    {
        $fields = [
            'full_name'            => $user->full_name_raw,
            'user_name'            => $user->user_name,
            'email'                => $user->email,
            'phone_number'         => $user->phone_number,
            'password'             => '**************',
            'language_id'          => $user->profile->language_id,
            'currency_id'          => $user->profile->currency_id,
            'logout_other_devices' => null,
            'cancel_account'       => null,
        ];

        try {
            $proxy = new stdClass();

            foreach ($fields as $key => $value) {
                $proxy->{$key} = $value;
            }

            app('events')->dispatch('user.account.override_settings', [$proxy, $user]);

            $fields = (array)$proxy;
        } catch (\Throwable $exception) {
            Log::error('override account setting fields error: ' . $exception->getMessage());
            Log::error('override account setting fields error trace: ' . $exception->getTraceAsString());
        }


        return $fields;
    }

    protected function handleFields(User $user, array $fields): array
    {
        $resourceData = [];
        $ordering     = $this->getAccountSettingOrdering();
        $actions      = $this->getAccountSettingActions();
        $dialogs      = $this->getAccountSettingDialogs();

        foreach ($fields as $name => $value) {
            if ($this->shouldSkipField($name, $user)) {
                continue;
            }

            $action   = $actions[$name] ?? $actions['default'];
            $isDialog = $dialogs[$name] ?? false;

            $data = [
                'id'          => $user->entityId(),
                'name'        => $name,
                'label'       => __p("user::phrase.account_setting_label.$name"),
                'value'       => $value,
                'action'      => $action,
                'ordering'    => $ordering[$name],
                'dialog'      => $isDialog,
                'description' => '',
            ];

            $data = $this->handleExtraData($user, $name, $data);

            $resourceData[] = $data;
        }

        return $resourceData;
    }

    protected function getAccountSettingOrdering(): array
    {
        return [
            'full_name'            => 1,
            'user_name'            => 2,
            'email'                => 3,
            'phone_number'         => 4,
            'password'             => 5,
            'language_id'          => 6,
            'currency_id'          => 7,
            'logout_other_devices' => 8,
            'cancel_account'       => 9,
        ];
    }

    protected function getAccountSettingActions(): array
    {
        return [
            'email'                => 'getEmailSettingForm',
            'phone_number'         => 'getPhoneNumberSettingForm',
            'cancel_account'       => 'getCancelAccountForm',
            'default'              => 'getAccountSettingForm',
            'logout_other_devices' => 'logoutOtherDevices',
        ];
    }

    protected function getAccountSettingDialogs(): array
    {
        return [
            'email'        => Settings::get('user.verify_after_changing_email'),
            'phone_number' => Settings::get('user.verify_after_changing_phone_number'),
        ];
    }

    protected function getLanguageIdSetting(User $user, array $data): array
    {
        $data['options'] = Language::getActiveOptions();
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getPasswordSetting(User $user, array $data): array
    {
        $data['type'] = MetaFoxForm::PASSWORD;

        return $data;
    }

    protected function getCurrencyIdSetting(User $user, array $data): array
    {
        $data['options'] = app('currency')->getActiveOptions();
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getCancelAccountSetting(User $user, array $data): array
    {
        $data['type']      = MetaFoxForm::LINK_BUTTON;
        $data['typeProps'] = [
            'color' => MetaFox::isMobile() ? 'danger' : 'error.main',
            'to'    => '/user/remove/',
        ];

        return $data;
    }

    protected function getLogoutOtherDevicesSetting(User $user, array $data): array
    {
        $data['type']      = MetaFoxForm::LINK_BUTTON;
        $data['typeProps'] = [
            'color'   => 'primary',
            'padding' => 0,
        ];

        return $data;
    }

    protected function handleExtraData(User $user, string $name, array $data): array
    {
        $studlyName        = Str::studly($name);
        $settingMethodName = "get{$studlyName}Setting";

        if (!method_exists($this, $settingMethodName)) {
            return $data;
        }

        return $this->{$settingMethodName}($user, $data);
    }

    protected function shouldSkipField(string $fieldName, User $user): bool
    {
        $studlyKey               = Str::studly($fieldName);
        $hasPermissionMethodName = "canEdit{$studlyKey}Setting";

        if (!method_exists($this, $hasPermissionMethodName)) {
            return false;
        }

        return !$this->{$hasPermissionMethodName}($user);
    }

    protected function canEditCancelAccountSetting(User $user): bool
    {
        if ($user->hasSuperAdminRole()) {
            return false;
        }

        return Gate::allows('delete', $user);
    }
}
