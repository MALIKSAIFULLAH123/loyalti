<?php

namespace MetaFox\Platform\Http\Resources\v1\Category\Admin;

use MetaFox\Core\Support\Facades\Language;

/**
 * Class AbstractUpdateCategoryForm.
 * @ignore
 * @codeCoverageIgnore
 */
abstract class AbstractUpdateCategoryForm extends AbstractStoreCategoryForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = $this->categoryRepository()->find($id);
    }

    protected function prepare(): void
    {
        $this->action($this->getActionUrl())
            ->asPut()
            ->title(__p('core::phrase.edit_category'))
            ->setValue($this->getValues());
    }

    protected function isDisabled(): bool
    {
        $defaultCategoryParentIds = $this->categoryRepository()->getDefaultCategoryParentIds();
        $isDisabled               = $this->resource->is_default;

        if (in_array($this->resource->entityId(), $defaultCategoryParentIds)) {
            $isDisabled = true;
        }

        return $isDisabled;
    }

    protected function getValues(): array
    {
        $model = $this->resource;
        return [
            'name'      => Language::getPhraseValues($model->name_var),
            'is_active' => $model->is_active,
            'ordering'  => $model->ordering,
            'parent_id' => $model->parent_id,
            'name_url'  => $model->name_url,
        ];
    }
}
