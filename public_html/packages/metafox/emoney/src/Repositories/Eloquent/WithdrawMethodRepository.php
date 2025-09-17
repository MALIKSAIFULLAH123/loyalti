<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\EMoney\Repositories\WithdrawMethodRepositoryInterface;
use MetaFox\EMoney\Models\WithdrawMethod;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class WithdrawMethodRepository.
 */
class WithdrawMethodRepository extends AbstractRepository implements WithdrawMethodRepositoryInterface
{
    public const METHOD_ITEM_CACHE_ID = 'ewallet_withdraw_method_items';
    public const METHOD_CACHE_ID      = 'ewallet_withdraw_methods';

    public function model()
    {
        return WithdrawMethod::class;
    }

    public function viewMethods(): Collection
    {
        return localCacheStore()->rememberForever(self::METHOD_CACHE_ID, function () {
            return $this->getModel()->newQuery()
                ->join('packages', function (JoinClause $joinClause) {
                    $joinClause->on('packages.alias', '=', 'emoney_withdraw_methods.module_id')
                        ->where([
                            'packages.is_active'    => 1,
                            'packages.is_installed' => 1,
                        ]);
                })
                ->get(['emoney_withdraw_methods.*'])
                ->keyBy('service');
        });
    }

    /**
     * @param  string                 $service
     * @return WithdrawMethod
     * @throws ModelNotFoundException
     */
    private function getMethod(string $service): WithdrawMethod
    {
        $method = $this->viewMethods()->get($service);

        if (null === $method) {
            throw new ModelNotFoundException();
        }

        return $method;
    }

    public function activateMethod(string $service, bool $active): bool
    {
        $method = $this->getMethod($service);

        $method->update(['is_active' => $active]);

        $this->clearCaches();

        return true;
    }

    private function clearCaches(): void
    {
        localCacheStore()->deleteMultiple([self::METHOD_CACHE_ID, self::METHOD_ITEM_CACHE_ID]);
    }
}
