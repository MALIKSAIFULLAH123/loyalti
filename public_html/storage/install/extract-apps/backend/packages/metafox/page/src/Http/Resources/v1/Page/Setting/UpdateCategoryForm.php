<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Page\Models\Page as Model;

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
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action("page/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
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
                    ->multiple(false)
                    ->valueType('number')
                    ->setRepository(PageCategoryRepositoryInterface::class)
                    ->setSelectedCategories(collect([$this->resource->category]))
                    ->setAttribute('disableClearable', true)
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
