<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use MetaFox\Localize\Repositories\CurrencyRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class CurrencyController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @group currency
 * @authenticated
 */
class CurrencyController extends ApiController
{
    public CurrencyRepositoryInterface $repository;

    /**
     * CurrencyController constructor.
     *
     * @param CurrencyRepositoryInterface $repository
     *
     * @ignore
     */
    public function __construct(CurrencyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
