<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserValue;

/**
 * Class ModerationRightMobileForm.
 * @property User              $resource
 * @property array<int, mixed> $settings the list of privacy settings
 *
 * @driverName group.group.moderation_right
 */
class ModerationRightMobileForm extends AbstractForm
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    public function boot(?int $id = null): void
    {
        $this->resource = UserEntity::getById($id)->detail;
        $this->data     = UserValue::getUserValueSettings($this->resource);
    }

    protected function prepare(): void
    {
        $value = [];

        foreach ($this->data as $name => $right) {
            $value[$name] = Arr::get($right, 'value');
        }

        $this->title(__('group::phrase.moderation_rights'))
            ->action(url_utility()->makeApiUrl('group/moderation-right/' . $this->resource->entityId()))
            ->asPut()
            ->setValue($value)
            ->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $this->addHeader(['showRightHeader' => false])
            ->component('FormHeader')
            ->autoSubmit();

        foreach ($this->data as $name => $setting) {
            $basic->addField(
                Builder::switch($name)
                    ->marginNone()
                    ->label($setting['phrase']),
            );
        }
    }
}
