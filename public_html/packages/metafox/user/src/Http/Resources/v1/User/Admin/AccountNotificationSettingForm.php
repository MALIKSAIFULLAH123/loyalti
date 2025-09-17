<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Notification\Repositories\Eloquent\NotificationChannelRepository;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class AccountNotificationSettingForm.
 *
 * @property Model $resource
 * @driverType form
 * @driverName user.update.notification
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountNotificationSettingForm extends AbstractForm
{
    public const MODULE_KEY = 'module_id';

    public const VAR_NAME_KEY = 'var_name';

    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values                 = [];
        $values['notification'] = $this->getDefaultNotificationValue();

        $this->action('admincp/user/notification-setting/' . $this->resource->id)
            ->asPatch()
            ->resetFormOnSuccess(false)
            ->setValue($values);
    }

    public function initialize(): void
    {
        $this->buildNotificationSections();
        $this->addDefaultFooter(true);
    }

    private function getNotificationChannel(): array
    {
        return resolve(NotificationChannelRepository::class)
            ->getActiveChannelNames();
    }

    private function getDefaultNotificationValue(): array
    {
        $values = [];

        foreach ($this->getNotificationChannel() as $channel) {
            $values[$channel] = $this->getDefaultNotificationValueByChannel($channel);
        }

        return $values;
    }

    private function getDefaultNotificationValueByChannel(string $channel): array
    {
        $moduleKey  = static::MODULE_KEY;
        $varNameKey = static::VAR_NAME_KEY;

        $settings = UserFacade::getNotificationSettingsByChannel($this->resource, $channel);

        $valueModule  = [];
        $valueVarName = [];

        foreach ($settings as $setting) {
            $this->getValueNotification($setting, $valueModule, $moduleKey);
            $types = Arr::get($setting, 'type', []);
            foreach ($types as $type) {
                $this->getValueNotification($type, $valueVarName, $varNameKey);
            }
        }

        return [
            $moduleKey  => $valueModule,
            $varNameKey => $valueVarName,
        ];
    }

    private function getValueNotification(array $params, array &$values, string $key): void
    {
        $key          = Arr::get($params, $key);
        $values[$key] = (int) Arr::get($params, 'value');
    }

    private function buildNotificationSwitchField(Section $basic, array $settings): void
    {
        foreach ($settings as $setting) {
            $this->buildModuleField($basic, $setting);
            $this->buildVarNameField($basic, $setting);
        }
    }

    private function buildNotificationSection(string $channel): void
    {
        $settings = UserFacade::getNotificationSettingsByChannel($this->resource, $channel);
        if (empty($settings)) {
            return;
        }

        $container = $this->addSection("notification_{$channel}")
            ->label(__p("notification::phrase.{$channel}_notifications"))
            ->collapsible()
            ->collapsed();

        $this->buildNotificationSwitchField($container, $settings);
    }

    private function buildNotificationSections(): void
    {
        foreach ($this->getNotificationChannel() as $channel) {
            $this->buildNotificationSection($channel);
        }
    }

    private function getFieldNameNotification(array $setting, string $keyName, string $name = null): string
    {
        $channel = Arr::get($setting, 'channel');

        if (null == $name) {
            $name = Arr::get($setting, $keyName);
        }

        return sprintf('notification.%s.%s.%s', $channel, $keyName, $name);
    }

    private function buildModuleField(Section $basic, array $setting): void
    {
        $moduleKey  = static::MODULE_KEY;
        $moduleName = $this->getFieldNameNotification($setting, $moduleKey);

        $basic->addField(
            Builder::checkbox($moduleName)
                ->label(Arr::get($setting, 'phrase'))
        );
    }

    private function buildVarNameField(Section $basic, array $setting): void
    {
        $moduleKey  = static::MODULE_KEY;
        $varNameKey = static::VAR_NAME_KEY;
        $types      = Arr::get($setting, 'type', []);
        foreach ($types as $type) {
            $moduleName = $this->getFieldNameNotification($setting, $moduleKey);
            $varName    = $this->getFieldNameNotification($setting, $varNameKey, Arr::get($type, $varNameKey));

            $basic->addField(
                Builder::checkbox($varName)
                    ->sxFieldWrapper(['pl' => 2])
                    ->label(Arr::get($type, 'phrase'))
                    ->showWhen(['truthy', $moduleName])
            );
        }
    }
}
