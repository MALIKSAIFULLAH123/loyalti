<?php

namespace MetaFox\Report\Http\Requests\v1\ReportOwner;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason'    => ['sometimes', 'numeric', 'exists:report_reasons,id'],
            'feedback'  => ['sometimes', 'string', 'nullable'],
            'item_id'   => ['required', 'numeric', 'min:1'],
            'item_type' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        $data['ip_address'] = parent::ip();

        if (isset($data['reason'])) {
            $data['reason_id'] = $data['reason'];
            unset($data['reason']);
        }

        return $data;
    }
}
