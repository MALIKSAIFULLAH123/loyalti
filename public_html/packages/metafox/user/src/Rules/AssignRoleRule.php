<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;

/**
 * Class ItemPrivacySettingRule.
 */
class AssignRoleRule implements Rule
{
    public function __construct(protected User $context) {}

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $targetRole): bool
    {
        if ($targetRole == UserRole::SUPER_ADMIN_USER && !$this->context->hasSuperAdminRole()) {
            return false;
        }

        $contextRole = $this->context->roleId();

        return $contextRole <= $targetRole;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('user::validation.insufficient_permission_to_update_role');
    }
}
