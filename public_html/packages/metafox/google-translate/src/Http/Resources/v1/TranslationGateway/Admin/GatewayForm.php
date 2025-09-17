<?php

namespace MetaFox\GoogleTranslate\Http\Resources\v1\TranslationGateway\Admin;

use MetaFox\Form\Builder;
use MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin\GatewayForm as AdminGatewayForm;
use MetaFox\Yup\Yup;

class GatewayForm extends AdminGatewayForm
{
    public function prepare(): void
    {
        parent::prepare();
    }

    protected function getGatewayConfigFields(): array
    {
        return [
            Builder::text('api_key')
                ->required()
                ->label(__p(('googletranslate::admin.api_key')))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                ),
        ];
    }

    protected function getValidationRules(): array
    {
        return array_merge(parent::getValidationRules(), [
            'api_key' => ['sometimes', 'string'],
        ]);
    }
}
