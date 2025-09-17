<?php

namespace MetaFox\Platform\Http\Resources\v1\Category\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class AbstractStoreCategoryForm.
 * @ignore
 * @codeCoverageIgnore
 */
abstract class AbstractStoreCategoryForm extends AbstractForm
{
    abstract protected function categoryRepository(): CategoryRepositoryInterface;

    abstract protected function getActionUrl(): string;

    public function boot(?int $id = null): void
    {
    }

    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('core::phrase.create_category'))
            ->action($this->getActionUrl());
    }

    /**
     * @return array<string,mixed>
     */
    protected function getParentCategoryOptions(): array
    {
        return $this->categoryRepository()->getCategoriesForStoreForm($this->resource);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic([]);
        
        $this->getNameField($basic);
        $this->getParentField($basic);
        $this->getActiveField($basic);

        $this->addDefaultFooter();
    }

    protected function isDisabled(): bool
    {
        return false;
    }

    /**
     * @param Section $section
     * @return void
     */
    protected function getActiveField(Section $section): void
    {
        $section->addField(Builder::checkbox('is_active')
            ->disabled($this->isDisabled())
            ->label(__p('core::phrase.is_active')));
    }

    /**
     * @param Section $section
     * @return void
     */
    protected function getParentField(Section $section): void
    {
        $section->addField(
            Builder::choice('parent_id')
                ->label(__p('core::phrase.parent_category'))
                ->required(false)
                ->options($this->getParentCategoryOptions())
        );
    }

    /**
     * @param Section $section
     * @return void
     */
    protected function getNameField(Section $section): void
    {
        $translatableComponent = Builder::translatableText('name')
            ->label(__p('core::phrase.name'))
            ->required(true)
            ->maxLength(MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH)
            ->yup(
                Yup::string()
                    ->required(__p('validation.this_field_is_a_required_field'))
            )
            ->buildFields();

        $section->addFields(
            $translatableComponent,
            Builder::slug('name_url')
                ->label(__p('core::phrase.slug'))
                ->required()
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH)
                ->mappingField($translatableComponent->defaultComponent()->getName())
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(3, $this->getSlugMessage())
                        ->label(__p('core::phrase.slug'))
                )
        );
    }

    protected function getSlugMessage(): string
    {
        return __p('validation.between.string', ['attribute' => '${path}', 'min' => 3, 'max' => MetaFoxConstant::DEFAULT_MAX_CATEGORY_TITLE_LENGTH]);
    }
}
