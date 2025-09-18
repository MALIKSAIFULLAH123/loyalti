<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class InfoForm.
 * @property Model $resource
 * @deprecated Mobile version than v1.9
 */
class InfoMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->asPut()
            ->title(__p('group::phrase.group_info'))
            ->action("group/{$this->resource->entityId()}")
            ->setValue([
                'name'        => $this->resource->name,
                'type_id'     => $this->resource->type_id,
                'category_id' => $this->resource->category_id,
                'vanity_url'  => $this->resource->profile_name,
                'reg_method'  => $this->resource->privacy_type,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $minGroupNameLength = Settings::get('group.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxGroupNameLength = Settings::get('group.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->label(__p('group::phrase.group_name'))
                ->placeholder(__p('group::phrase.fill_in_a_name_for_your_group'))
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->maxLength(
                            $maxGroupNameLength,
                            __p('validation.field_must_be_at_most_max_length_characters', [
                                'field'     => __p('group::phrase.group_name'),
                                'maxLength' => $maxGroupNameLength,
                            ])
                        )
                        ->minLength(
                            $minGroupNameLength,
                            __p('validation.field_must_be_at_least_min_length_characters', [
                                'field'     => __p('group::phrase.group_name'),
                                'minLength' => $minGroupNameLength,
                            ])
                        )
                ),
            Builder::category('category_id')
                ->required()
                ->label(__p('core::phrase.category'))
                ->setRepository(CategoryRepositoryInterface::class)
                ->setSelectedCategories(collect([$this->resource->category]))
                ->multiple(false)
                ->valueType('number')
                ->yup(Yup::number()->required()),
            Builder::text('vanity_url')
                ->label(__p('core::phrase.url'))
                ->placeholder(__p('core::phrase.url'))
                ->description(__p('group::phrase.description_edit_group_url'))
                ->setAttribute('contextualDescription', url_utility()->makeApiFullUrl(''))
                ->findReplace([
                    'find'    => MetaFoxConstant::SLUGIFY_FILTERS,
                    'replace' => MetaFoxConstant::SLUGIFY_FILTERS_REPLACE,
                ]),
        );
    }
}
