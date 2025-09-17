<?php

namespace MetaFox\Activity\Http\Resources\v1\Type\Admin;

use Illuminate\Support\Arr;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Models\Type as Model;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateTypeForm.
 * @property Model $resource
 */
class UpdateTypeForm extends AbstractForm
{
    public function boot(int $id): void
    {
        $this->resource = resolve(TypeRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $this->asPut()
            ->title(__p('activity::phrase.edit_activity_type', ['title' => __p($this->resource->title)]))
            ->action('admincp/feed/type/' . $this->resource->id)
            ->setValue(new TypeItem($this->resource));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $valueDefault = $this->resource->value_default;
        /**
         * @var TypeManager $typeManager
         */
        $typeManager    = resolve(TypeManager::class);
        $allowAbilities = $typeManager->getAllowAbilities();

        foreach ($this->getSettings() as $key => $setting) {
            if ($typeManager->isDisabled($this->resource->type, $key)) {
                continue;
            }

            if (isset($valueDefault['hidden_keys']) && count($valueDefault['hidden_keys']) > 0) {
                if (in_array($key, $valueDefault['hidden_keys'])) {
                    continue;
                }
            }

            $isDisable = false;
            $typeAllow = Arr::get($allowAbilities, $key);

            if ($typeAllow !== null && in_array($typeAllow, $valueDefault)) {
                $isDisable = !Arr::get($valueDefault, $typeAllow, true);
            }

            $builder = Builder::checkbox($key)
                ->disabled($isDisable)
                ->label(Arr::get($setting, 'label'));

            $basic->addFields($isDisable
                ? $builder->description(__p('core::phrase.this_setting_is_not_configurable'))
                : $builder);
        }

        $this->addDefaultFooter(true);
    }

    protected function getSettings(): array
    {
        return [
            'is_active'                             => [
                'label' => __p('activity::phrase.enable_this_activity_type'),
            ],
            /*'is_system' => [
                'label' => __p('activity::phrase.is_system_activity_type'),
            ],*/
            Type::CAN_COMMENT_TYPE                  => [
                'label' => __p('activity::phrase.activity_type_can_comment'),
            ],
            Type::CAN_LIKE_TYPE                     => [
                'label' => __p('activity::phrase.activity_type_can_like'),
            ],
            Type::CAN_SHARE_TYPE                    => [
                'label' => __p('activity::phrase.activity_type_can_share'),
            ],
            Type::CAN_EDIT_TYPE                     => [
                'label' => __p('activity::phrase.activity_type_can_edit'),
            ],
            Type::CAN_CREATE_FEED_TYPE              => [
                'label' => __p('activity::phrase.activity_type_can_create_feed'),
            ],
            Type::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE => [
                'label' => __p('activity::admin.can_change_privacy_from_feed'),
            ],
            Type::CAN_REDIRECT_TO_DETAIL_TYPE       => [
                'label' => __p('activity::phrase.activity_type_can_redirect_to_detail'),
            ],
            Type::PREVENT_EDIT_FEED_ITEM_TYPE       => [
                'label' => __p('activity::admin.prevent_from_edit_feed_item'),
            ],
        ];
    }
}
