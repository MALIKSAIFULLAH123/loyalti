<?php

namespace MetaFox\Report\Http\Requests\v1\ReportItem;

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
            'reason'    => ['required', 'numeric', 'exists:report_reasons,id'],
            'feedback'  => ['sometimes', 'string', 'nullable'],
            'item_id'   => ['required', 'numeric'],
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

    public function messages(): array
    {
        return [
            'reason.required' => __p('report::phrase.please_choose_a_reason_for_this_reported_item'),
        ];
    }
}
