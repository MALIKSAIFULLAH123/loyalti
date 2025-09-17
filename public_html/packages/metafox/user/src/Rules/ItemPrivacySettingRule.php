<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * Class ItemPrivacySettingRule.
 */
class ItemPrivacySettingRule implements Rule
{
    /** @var array<string, mixed> */
    private array $allows;

    public function __construct(int $userId)
    {
        $service = resolve(UserPrivacyRepositoryInterface::class);
        $this->allows = $service->getItemPrivacySettings($userId);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        return array_key_exists($value, $this->allows);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('user::validation.cannot update_item_privacy_setting');
    }
}
