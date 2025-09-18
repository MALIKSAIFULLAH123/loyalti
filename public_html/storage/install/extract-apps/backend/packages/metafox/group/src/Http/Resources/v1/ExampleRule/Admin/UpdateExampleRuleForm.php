<?php

namespace MetaFox\Group\Http\Resources\v1\ExampleRule\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Group\Models\ExampleRule as Model;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateExampleRuleForm.
 * @property ?Model $resource
 */
class UpdateExampleRuleForm extends StoreExampleRuleForm
{
    /** @var bool */
    protected $isEdit = false;

    public function boot(ExampleRuleRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->asPut()
            ->title(__p('group::phrase.edit_example_rule'))
            ->action(url_utility()->makeApiUrl('admincp/group/example-rule/' . $this->resource->id))
            ->setValue([
                'title'       => Language::getPhraseValues($this->resource->title_var),
                'description' => Language::getPhraseValues($this->resource->description_var),
                'is_active'   => $this->resource->is_active,
            ]);
    }
}
