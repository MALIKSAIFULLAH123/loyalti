<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class AbstractDestroyCategoryForm.
 */
abstract class AbstractDestroyCategoryForm extends AbstractForm
{
    /**
     * @var CategoryRepositoryInterface
     *                                  warn: unitest might not assign mock there.
     */
    protected $repository;

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.delete_category'))
            ->action($this->getActionUrl())
            ->asDelete()
            ->setValue([
                'migrate_items' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic([]);

        $this->handleConfirm($basic);

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.delete')),
                Builder::cancelButton(),
            );
    }

    /**
     * @return array<string,mixed>
     */
    protected function getParentCategoryOptions(): array
    {
        return $this->repository->getParentOptionsForDeleteWithMigration($this->resource);
    }

    /**
     * @return array
     */
    protected function getDeleteOptions(): array
    {
        $categoryOptions = $this->getParentCategoryOptions();

        $options[] = [
            'label' => $this->deleteAllItemOptionLabel(),
            'value' => 0,
        ];

        if ($categoryOptions) {
            array_push($options, [
                'label' => $this->moveAllItemOptionLabel(),
                'value' => 1,
            ]);
        }

        return $options;
    }

    /**
     * @return string
     */
    abstract protected function getActionUrl(): string;

    /**
     * @return string
     */
    abstract protected function getPluralizationItemType(): string;

    protected function handleConfirm(Section $basic): Section
    {
        $basic           = $this->addBasic([]);
        $categoryOptions = $this->getParentCategoryOptions();
        $deleteOptions   = $this->getDeleteOptions();

        $totalItem = $this->resource->total_item;

        if ($totalItem == 0 && !$this->resource->subCategories()->exists()) {
            return $basic->addFields(
                Builder::typography('delete_confirm')
                    ->tagName('strong')
                    ->plainText($this->deleteConfirm())
            );
        }

        $basic->addFields(
            Builder::typography('delete_confirm')
                ->tagName('strong')
                ->plainText($this->deleteConfirm()),
            Builder::description('delete_notice')
                ->label(__p('core::phrase.action_cant_be_undone')),
            Builder::radioGroup('migrate_items')
                ->label($this->deleteCategoryOptionLabel())
                ->options($deleteOptions)
                ->yup(Yup::string()->required()),
        );

        if ($categoryOptions) {
            $basic->addField(Builder::choice('new_category_id')
                ->label($this->categoryOptionLabel())
                ->requiredWhen(['eq', 'migrate_items', 1])
                ->showWhen(['eq', 'migrate_items', 1])
                ->options($categoryOptions)
                ->yup(
                    Yup::number()
                        ->positive()
                        ->nullable(true)
                ));
        }

        return $basic;
    }

    protected function deleteConfirm(): string
    {
        return __p('core::phrase.delete_category_confirm', ['name' => $this->resource->name]);
    }

    protected function deleteCategoryOptionLabel(): string
    {
        return __p('core::phrase.delete_category_option_label', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function deleteAllItemOptionLabel(): string
    {
        return __p('core::phrase.delete_category_option_delete_all_items', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function moveAllItemOptionLabel(): string
    {
        return __p('core::phrase.delete_category_option_move_all_items', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function categoryOptionLabel(): string
    {
        return __p('core::phrase.category');
    }
}
