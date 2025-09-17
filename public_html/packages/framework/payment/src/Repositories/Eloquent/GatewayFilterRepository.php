<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Payment\Repositories\GatewayFilterRepositoryInterface;
use MetaFox\Payment\Models\GatewayFilter;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class GatewayFilterRepository.
 */
class GatewayFilterRepository extends AbstractRepository implements GatewayFilterRepositoryInterface
{
    public function model()
    {
        return GatewayFilter::class;
    }
}
