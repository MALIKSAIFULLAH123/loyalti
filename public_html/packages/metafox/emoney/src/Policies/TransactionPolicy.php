<?php

namespace MetaFox\EMoney\Policies;

use MetaFox\EMoney\Models\Transaction;
use MetaFox\Platform\Contracts\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($transaction->ownerId() == $user->entityId()) {
            return true;
        }

        return false;
    }
}
