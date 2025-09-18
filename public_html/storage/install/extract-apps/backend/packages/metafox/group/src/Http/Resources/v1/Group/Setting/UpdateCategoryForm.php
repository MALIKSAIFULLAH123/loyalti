<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Group\Models\Group as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateCategoryForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCategoryForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action("group/{$this->resource->entityId()}")
            ->secondAction('group/updateGroupInfo')
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
                    ->multiple(false)
                    ->valueType('number')
                    ->label(__p('core::phrase.category'))
                    ->setRepository(CategoryRepositoryInterface::class)
                    ->setSelectedCategories(collect([$this->resource->category]))
                    ->yup(Yup::number()->required()),
            );

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
