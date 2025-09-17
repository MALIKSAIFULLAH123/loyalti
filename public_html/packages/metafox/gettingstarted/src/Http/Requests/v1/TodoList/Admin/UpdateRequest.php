<?php

namespace MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin;

use MetaFox\GettingStarted\Rules\MaxItemResolutionRule;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

class UpdateRequest extends StoreRequest
{
    public function rules()
    {
        $data = parent::rules();

        $data['title'] = ['sometimes', 'array', new TranslatableTextRule()];
        $data['text']  = ['sometimes', 'array', new TranslatableTextRule()];

        if (isset($data['resolution'])) {
            unset($data['resolution']);
        }

        return $data;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (isset($data['resolution'])) {
            unset($data['resolution']);
        }

        return $data;
    }
}
