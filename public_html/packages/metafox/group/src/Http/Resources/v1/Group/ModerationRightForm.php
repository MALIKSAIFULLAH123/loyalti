<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserValue;

/**
 * Class ModerationRightForm.
 * @property int               $id       user id
 * @property array<int, mixed> $settings the list of privacy settings
 */
class ModerationRightForm extends AbstractForm
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    public function boot(?int $id = null): void
    {
        $this->resource = UserEntity::getById($id)->detail;
        $this->data = UserValue::getUserValueSettings($this->resource);
    }

    protected function prepare(): void
    {
        $value = [];

        foreach ($this->data as $name => $right) {
            $value[$name] = Arr::get($right, 'value');
        }

        $this
            ->title(__('group::phrase.moderation_rights'))
            ->action(url_utility()->makeApiUrl('group/moderation-right/' . $this->resource->entityId()))
            ->asPut()
            ->setValue($value)
            ->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        foreach ($this->data as $settingName => $setting) {
            $basic->addFields(
                Builder::switch($settingName)
                    ->label($setting['phrase'])
            );
        }
    }
}
