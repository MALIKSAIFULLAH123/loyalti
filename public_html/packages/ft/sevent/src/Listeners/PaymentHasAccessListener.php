<?php

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Models\Invoice;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Repositories\InvoiceRepositoryInterface;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class PaymentHasAccessListener
{
    public function __construct(
        protected SeventRepositoryInterface $repository,
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {
    }

    public function handle(?User $context, string $entityType, int $entityId, Gateway $gateway): ?bool
    {
        if (!in_array($entityType, [Invoice::ENTITY_TYPE, Sevent::ENTITY_TYPE, Ticket::ENTITY_TYPE])) {
            return null;
        }

        if ($gateway->service == 'activitypoint') {
            return (bool) Settings::get('sevent.enable_activity_point', false);
        }

        return (bool) true;
    }
}