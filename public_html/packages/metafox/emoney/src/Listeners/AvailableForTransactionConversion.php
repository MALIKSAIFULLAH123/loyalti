<?php
namespace MetaFox\EMoney\Listeners;

use MetaFox\Platform\Contracts\User;

class AvailableForTransactionConversion
{
    public function handle(User $context): bool
    {
        return true;
    }
}
