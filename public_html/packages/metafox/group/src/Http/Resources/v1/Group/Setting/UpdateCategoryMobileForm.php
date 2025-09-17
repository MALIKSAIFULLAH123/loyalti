<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateCategoryMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCategoryMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.label.category_id'))
            ->action("group/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/group')
            ->asPut()
            ->setValue([
                'category_id' => $this->resource->category_id,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                Builder::category('category_id')
                    ->required()
                    ->label(__p('core::phrase.category'))
                    ->setRepository(CategoryRepositoryInterface::class)
                    ->setSelectedCategories(collect([$this->resource->category]))
                    ->multiple(false)
                    ->valueType('number')
                    ->yup(Yup::number()->required()),
            );
    }
}
