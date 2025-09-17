<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationSetting;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Featured\Models\Item as Model;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Notification\Http\Requests\v1\NotificationSetting\UpdateRequest;
use MetaFox\Notification\Repositories\SettingRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\User\Support\User as UserSupport;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class NotificationSettingForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationSettingForm extends AbstractForm
{
    protected string $channel;
    protected array  $settings;

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function boot(Request $request)
    {
        $params = $request->all();
        if (Arr::has($params, 'channel')) {
            $this->setChannel(Arr::get($params, 'channel'));
        }

        $settings = $this->typeRepository()->getNotificationSettingsByChannel(user(), $this->getChannel());
        $this->setSettings($settings);
    }

    protected function prepare(): void
    {
        $config = $this->action('notification/setting')
            ->asPut()
            ->submitOnValueChanged()
            ->setValue($this->getValues());

        $this->getTitle() !== null ? $config->title($this->getTitle()) : $config->noHeader();
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            $this->getChannelSettingField(),
        );

        foreach ($this->getSettings() as $setting) {
            $fields = [];

            foreach ($setting['type'] as $value) {
                $fields[] = $this->buildSubField($value);

                $fields[] = Builder::divider('divider_' . $this->getTypeName($value['var_name']));
            }

            $this->buildSection($setting)
                ->showWhen(['truthy', $this->getChannelSettingName()])
                ->addFields(
                    $this->buildSubSection($setting)->addFields(...$fields)
                );
        }
    }

    protected function buildSection(array $setting): Section
    {
        return $this->addSection('entity_' . $setting['module_id'])
            ->name('entity_' . $setting['module_id'])
            ->label($setting['app_name'])
            ->collapsed()
            ->collapsible()
            ->addFields(...$this->buildFieldInSection($setting));
    }

    protected function buildFieldInSection(array $setting): array
    {
        return [
            Builder::switch($this->getModuleName($setting['module_id']))
                ->label($setting['phrase'])
                ->setAttribute('sxLabel', [
                    'pl' => 2,
                    'pb' => 2,
                ])
                ->marginDense(),
            Builder::divider('divider_' . $setting['module_id']),
        ];
    }

    protected function buildSubSection(array $setting): Section
    {
        $section = new Section();
        return $section->name('sub_' . $setting['module_id'])
            ->showWhen(['truthy', $this->getModuleName($setting['module_id'])])
            ->sx([
                'pl' => 4,
            ]);
    }

    protected function buildSubField(array $setting): AbstractField
    {
        return Builder::switch($this->getTypeName($setting['var_name']))
            ->label($setting['phrase'])
            ->setAttribute('sxLabel', [
                'pb' => 1,
            ])
            ->marginDense();
    }

    protected function getValues(): array
    {
        $context          = user();
        $values           = [];
        $subscribeSetting = $this->notificationSettingRepository()->getChannelsSubscribed($context);

        Arr::set($values, 'channel', $this->getChannel());
        Arr::set($values, $this->getChannelSettingName(), in_array($this->getChannel(), $subscribeSetting));

        foreach ($this->getSettings() as $setting) {
            foreach ($setting['type'] as $value) {
                Arr::set($values, $this->getTypeName($value['var_name']), $value['value']);
            }

            Arr::set($values, $this->getModuleName($setting['module_id']), $setting['value']);
        }

        return $values;
    }

    protected function getTypeName(string $name): string
    {
        return sprintf('var_name.%s', $name);
    }

    protected function getModuleName(string $name): string
    {
        return sprintf('module_id.%s', $name);
    }

    protected function getChannelSettingName(): ?string
    {
        return sprintf('setting.%s', UserSupport::SUBSCRIBE_NOTIFICATION_CHANNELS);
    }

    /**
     * validated.
     *
     * @param Request $request
     *
     * @return array<mixed>
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $params  = Arr::dot($request->all());
        $values  = Arr::dot($this->getValues());
        $changes = array_diff_assoc($params, $values);
        $params  = Arr::mapWithKeys(Arr::undot($changes), function ($value, $key) {
            $varName = key($value);
            return [
                $key      => $varName,
                'value'   => Arr::get($value, $varName),
                'channel' => $this->getChannel(),
            ];
        });

        $request   = new UpdateRequest();
        $rules     = $request->rules();
        $validator = Validator::make($params, $rules);

        return $validator->validate();
    }

    protected function getChannelSettingField(): AbstractField
    {
        return Builder::switch($this->getChannelSettingName())
            ->label(__p('notification::phrase.do_you_want_to_subscribe_notifications', [
                'channel' => $this->getChannel(),
            ]))
            ->setAttribute('sxLabel', [
                'pb' => 2,
            ])
            ->marginDense();
    }

    protected function getTitle(): ?string
    {
        return null;
    }

    protected function notificationSettingRepository(): SettingRepositoryInterface
    {
        return resolve(SettingRepositoryInterface::class);
    }

    protected function typeRepository(): TypeRepositoryInterface
    {
        return resolve(TypeRepositoryInterface::class);
    }
}
