<?php

namespace MetaFox\Advertise\Http\Requests\v1\Placement\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

class DeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'             => ['required', 'numeric', 'exists:advertise_placements'],
            'delete_option'  => ['required', new AllowInRule($this->getDeleteOptions())],
            'alternative_id' => ['required_if:delete_option,' . Support::DELETE_MIGRATION, 'numeric', 'exists:advertise_placements,id'],
        ];
    }

    protected function getDeleteOptions(): array
    {
        return array_column(Facade::getDeleteOptions(), 'value');
    }
}
