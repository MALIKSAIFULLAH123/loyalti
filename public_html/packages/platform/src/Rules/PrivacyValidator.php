<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Class PrivacyValidator.
 */
class PrivacyValidator implements Rule
{
    /**
     * @var array<int, mixed>
     */
    private array $allows = [
        MetaFoxPrivacy::EVERYONE,
        MetaFoxPrivacy::MEMBERS,
        MetaFoxPrivacy::FRIENDS,
        MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
        MetaFoxPrivacy::ONLY_ME,
        MetaFoxPrivacy::CUSTOM,
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $value = (int) $value;

        return in_array($value, $this->getAllows(), true);
    }

    /**
     * @return array<int, mixed>
     */
    public function getAllows(): array
    {
        return $this->allows;
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
