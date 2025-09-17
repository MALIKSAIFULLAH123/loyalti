<?php

namespace Foxexpert\Sevent\Policies;

use Foxexpert\Sevent\Models\Invoice;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Platform\Contracts\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ?Invoice $invoice): bool
    {
        return true;
    }

    public function change(User $user, ?Invoice $invoice, bool $checkRepayment = true): bool
    {
        return true;
    }
}
