<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateNameMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateNameMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p("group::phrase.label.name"))
            ->action("group/{$this->resource->entityId()}")
            ->asPut()
            ->secondAction('@updatedItem/group')
            ->setValue([
                'name' => $this->resource->name,
            ]);
    }

    protected function initialize(): void
    {
        $minGroupNameLength = Settings::get('group.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxGroupNameLength = Settings::get('group.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $this->addBasic()
            ->addField(
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
            );
    }
}
