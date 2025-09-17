<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * Class ProfileSettingRule.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ProfileSettingRule implements Rule
{
    /** @var array<string, mixed> */
    private array $allows;

    public function __construct(int $userId)
    {
        $service = resolve(UserPrivacyRepositoryInterface::class);
        $this->allows = $service->getProfileSettings($userId);
    }

    public function passes($attribute, $value): bool
    {
        return array_key_exists($value, $this->allows);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('user::validation.cannot_update_profile_setting');
    }
}
