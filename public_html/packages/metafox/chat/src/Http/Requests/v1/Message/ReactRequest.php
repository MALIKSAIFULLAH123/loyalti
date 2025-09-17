<?php
namespace MetaFox\Chat\Http\Requests\v1\Message;

use Illuminate\Foundation\Http\FormRequest;

class ReactRequest extends FormRequest
{
    public function rules()
    {
        return [
          'react' => ['sometimes', 'string'],
          'remove' => ['sometimes', 'boolean']
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['react'])) {
            $data['react'] = '';
        }

        if (!isset($data['remove'])) {
            $data['remove'] = true;
        }

        return $data;
    }
}
