<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\MetaFoxConstant;

class HexColorRule implements Rule
{
    public function passes($attribute, $value)
    {
        return (bool) preg_match('/' . MetaFoxConstant::HEX_COLOR_REGEX . '/', $value);
    }

    public function message()
    {
        return __p('validation.regex');
    }
}
