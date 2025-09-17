<?php

namespace MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Translation\Models\TranslationGateway as Model;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GatewayForm.
 * @property ?Model $resource
 */
class GatewayForm extends AdminSettingForm
{
    public function boot(?int $id = null): void
    {
        if ($id) {
            $this->resource = resolve(TranslationGatewayRepositoryInterface::class)->find($id);
        }
    }

    /**
     * validated.
     *
     * @param Request $request
     * @return array<mixed>
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        $rules = $this->getValidationRules();

        $validator = Validator::make($data, $rules);

        $validator->validate();

        if (Arr::has($data, 'description')) {
            unset($data['description']);
        }

        return $data;
    }

    /**
     * getValidationRules.
     *
     * @return array<string, array<mixed>>
     */
    protected function getValidationRules(): array
    {
        return [
            'title'     => ['required', 'string', 'between:2,255'],
            'is_active' => ['sometimes', new AllowInRule([true, false, 0, 1])],
        ];
    }

    protected function prepare(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $this->title(__p('translation::phrase.edit_translation_gateway'))
            ->action(apiUrl('admin.translation.gateway.update', ['gateway' => $this->resource->id]))
            ->asPut();

        if ($this->resource instanceof Model) {
            $this->setValue(array_merge([
                'title'       => $this->resource->title,
                'description' => $this->resource->description,
                'is_active'   => $this->resource->is_active,
            ], $this->resource->config));
        }
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label(__p(('core::phrase.title')))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                ),
        );

        $fields = $this->getGatewayConfigFields();
        if (!empty($fields)) {
            $basic->addFields(...$fields);
        }

        $basic->addField(
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active')),
        );

        $this->addDefaultFooter();
    }

    /**
     * getGatewayConfigFields.
     *
     * @return array<FormField>
     */
    protected function getGatewayConfigFields(): array
    {
        return [];
    }
}

