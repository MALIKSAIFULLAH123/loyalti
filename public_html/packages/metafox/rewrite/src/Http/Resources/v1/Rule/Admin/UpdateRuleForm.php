<?php

namespace MetaFox\Rewrite\Http\Resources\v1\Rule\Admin;

use MetaFox\Rewrite\Models\Rule as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateRuleForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateRuleForm extends StoreRuleForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('core::phrase.edit'))
            ->action('/admincp/rewrite/rule/' . $this->resource->id)
            ->asPut()
            ->setValue([
                'from_path'      => $this->resource->from_path,
                'to_mobile_path' => $this->resource->to_mobile_path,
                'to_path'        => $this->resource->to_path,
                'module_id'      => $this->resource->module_id,
            ]);
    }
}
