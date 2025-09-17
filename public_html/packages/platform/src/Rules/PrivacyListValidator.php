<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class PrivacyListValidator.
 */
class PrivacyListValidator implements Rule
{
    /**
     * @param  string $attribute
     * @param  int[]  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value)
    {
        $response = app('events')->until('core.check_privacy_list', [
            $value,
            Auth::id(),
            'friend_list',
        ]);

        return !empty($response);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __p('validation.invalid_privacy');
    }
}
