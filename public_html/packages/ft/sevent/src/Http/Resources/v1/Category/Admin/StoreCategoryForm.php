<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Category\Admin;

use Foxexpert\Sevent\Models\Category as Model;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class StoreCategoryForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreCategoryForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('core::phrase.create_category'))
            ->action(url_utility()->makeApiUrl('admincp/sevent/category'));
    }

    /**
     * @return array<string,mixed>
     */
    protected function getParentCategoryOptions(): array
    {
        return resolve(CategoryRepositoryInterface::class)->getCategoriesForStoreForm($this->resource);
    }

    protected function initialize(): void
    {
        $basic                 = $this->addBasic([]);
        $translatableComponent = Builder::translatableText('name')
            ->label(__p('core::phrase.name'))
            ->required(true)
            ->maxLength(MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH)
            ->yup(
                Yup::string()
                    ->required(__p('validation.this_field_is_a_required_field'))
            )
            ->buildFields();
        $slugMessage = __p('validation.between.string', ['attribute' => '${path}', 'min' => 3, 'max' => MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH]);

        $basic->addFields(
            $translatableComponent,
            Builder::slug('name_url')
                ->label(__p('core::phrase.slug'))
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH)
                ->required()
                ->mappingField($translatableComponent->defaultComponent()->getName())
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(3, $slugMessage)
                        ->label(__p('core::phrase.slug'))
                ),
            Builder::choice('parent_id')
                ->label(__p('core::phrase.parent_category'))
                ->required(false)
                ->options($this->getParentCategoryOptions()),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active'))
                ->disabled($this->isDisabled())
        );

        $this->addDefaultFooter();
    }

    protected function isDisabled(): bool
    {
        return false;
    }
}
