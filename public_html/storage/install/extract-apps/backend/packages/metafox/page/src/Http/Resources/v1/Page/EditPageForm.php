<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\Contracts\ResourceText;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditPageForm.
 * @property Model $resource
 */
class EditPageForm extends CreatePageForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;

        $textResource = $resource->pageText;

        $this
            ->title(__('page::phrase.edit_page'))
            ->action(url_utility()->makeApiUrl('page/' . $resource->entityId()))
            ->asPut()
            ->setValue([
                'name'        => $resource->name,
                'description' => $textResource instanceof ResourceText ? $textResource->text_parsed : null,
                'category_id' => $resource->category_id,
            ]);
    }
}
