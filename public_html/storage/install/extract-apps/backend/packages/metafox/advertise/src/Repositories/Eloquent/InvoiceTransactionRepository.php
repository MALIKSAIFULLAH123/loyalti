<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\InvoiceTransactionRepositoryInterface;
use MetaFox\Advertise\Models\InvoiceTransaction;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InvoiceTransactionRepository.
 */
class InvoiceTransactionRepository extends AbstractRepository implements InvoiceTransactionRepositoryInterface
{
    public function model()
    {
        return InvoiceTransaction::class;
    }
}
