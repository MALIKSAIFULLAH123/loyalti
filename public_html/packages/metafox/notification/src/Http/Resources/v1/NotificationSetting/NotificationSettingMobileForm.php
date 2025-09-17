<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationSetting;

use MetaFox\Featured\Models\Item as Model;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder as Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class NotificationSettingMobileForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationSettingMobileForm extends NotificationSettingForm
{
    protected function initialize(): void
    {
        $this->addBasic()->addField($this->getChannelSettingField());

        foreach ($this->getSettings() as $setting) {
            $fields = [];

            foreach ($setting['type'] as $value) {
                $fields[] = $this->buildSubField($value);
            }

            $this->buildSection($setting)
                ->showWhen(['truthy', $this->getChannelSettingName()])
                ->addFields(
                    $this->buildSubSection($setting)->addFields(...$fields)
                );
        }
    }

    protected function buildFieldInSection(array $setting): array
    {
        return [
            Builder::switch($this->getModuleName($setting['module_id']))
                ->label($setting['phrase'])
                ->setAttribute('paddingLeft', 'normal')
                ->marginNone(),
        ];
    }

    protected function buildSubField(array $setting): AbstractField
    {
        return Builder::switch($this->getTypeName($setting['var_name']))
            ->label($setting['phrase'])
            ->setAttribute('paddingLeft', 'large')
            ->marginNone();
    }

    protected function getChannelSettingField(): AbstractField
    {
        return Builder::switch($this->getChannelSettingName())
            ->label(__p('notification::phrase.do_you_want_to_subscribe_notifications', [
                'channel' => $this->channel,
            ]))
            ->marginNone();
    }

    protected function getTitle(): ?string
    {
        return __p('notification::web.channel_notifications', ['channel' => $this->channel]);
    }
}
