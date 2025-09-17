<?php

namespace MetaFox\Paypal\Listeners;

use MetaFox\Platform\Contracts\User;

class AccountSettingValuesListener
{
    public function handle(User $context): ?array
    {
        $values = app('events')->dispatch('payment.user.configuration', [$context->entityId(), 'paypal'], true);

        if (null === $values) {
            return null;
        }

        return [
            'paypal' => ['value' => $values],
        ];
    }
}
