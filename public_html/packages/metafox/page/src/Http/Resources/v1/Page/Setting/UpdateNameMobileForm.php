<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
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
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('page::phrase.label.name'))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/page')
            ->asPut()
            ->setValue([
                'name' => $this->resource->name,
            ]);
    }

    protected function initialize(): void
    {
        $minPageNameLength = Settings::get('page.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxPageNameLength = Settings::get('page.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.title'))
                    ->minLength($minPageNameLength)
                    ->maxLength($maxPageNameLength)
                    ->placeholder(__p('page::phrase.fill_in_a_name_for_your_page'))
                    ->yup(
                        Yup::string()
                            ->required(__p('validation.this_field_is_a_required_field'))
                            ->maxLength(
                                $maxPageNameLength,
                                __p('validation.field_must_be_at_most_max_length_characters', [
                                    'field'     => __p('page::phrase.page_name'),
                                    'maxLength' => $maxPageNameLength,
                                ])
                            )
                            ->minLength(
                                $minPageNameLength,
                                __p('validation.field_must_be_at_least_min_length_characters', [
                                    'field'     => __p('page::phrase.page_name'),
                                    'minLength' => $minPageNameLength,
                                ])
                            )
                    ),
            );
    }
}
