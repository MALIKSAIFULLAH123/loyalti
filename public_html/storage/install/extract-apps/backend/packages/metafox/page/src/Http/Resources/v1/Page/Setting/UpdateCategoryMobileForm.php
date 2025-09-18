<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
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
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('page::phrase.label.category_id'))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/page')
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
                    ->yup(Yup::string()->required())
                    ->setRepository(PageCategoryRepositoryInterface::class)
                    ->setSelectedCategories(collect([$this->resource->category]))
                    ->multiple(false)
                    ->valueType('number'),
            );
    }
}
