<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Contracts\User;

class PaymentHasAccessListener
{
    public function __construct(
        protected ListingRepositoryInterface $repository,
        protected InvoiceRepositoryInterface $invoiceRepository
    ) {
    }

    public function handle(?User $context, string $entityType, int $entityId, Gateway $gateway): ?bool
    {
        if (!in_array($entityType, [Invoice::ENTITY_TYPE, Listing::ENTITY_TYPE])) {
            return null;
        }

        $listing = match ($entityType) {
            Listing::ENTITY_TYPE => $this->repository->find($entityId),
            Invoice::ENTITY_TYPE => $this->invoiceRepository->find($entityId)->listing,
        };

        if ($listing === null) {
            return false;
        }

        if (!policy_check(ListingPolicy::class, 'payment', $context, $listing)) {
            return false;
        }

        if ($gateway->service == 'activitypoint') {
            return (bool) $listing->allow_point_payment;
        }

        return (bool) $listing->allow_payment;
    }
}
