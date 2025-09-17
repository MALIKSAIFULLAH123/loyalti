<?php
namespace MetaFox\EMoney\Policies;

use MetaFox\Platform\Contracts\User;

class UserBalancePolicy
{
    public function send(User $context, User $user): bool
    {
        if (!$this->beforeAdjustment($context)) {
            return false;
        }

        return true;
    }

    public function reduce(User $context, User $user): bool
    {
        if (!$this->beforeAdjustment($context)) {
            return false;
        }

        return true;
    }

    public function beforeAdjustment(User $context): bool
    {
        if ($context->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function viewHistories(User $context): bool
    {
        if (!$this->beforeAdjustment($context)) {
            return false;
        }

        return true;
    }
}
