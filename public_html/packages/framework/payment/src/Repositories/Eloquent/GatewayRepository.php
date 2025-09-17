<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\GatewayFilter;
use MetaFox\Payment\Policies\GatewayPolicy;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * Class GatewayRepository.
 * @method Gateway getModel()
 * @method Gateway find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class GatewayRepository extends AbstractRepository implements GatewayRepositoryInterface
{
    public function model(): string
    {
        return Gateway::class;
    }

    public function viewGateway(User $context, int $id): Gateway
    {
        policy_authorize(GatewayPolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewGateways(User $context, array $attributes): Paginator
    {
        policy_authorize(GatewayPolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'] ?? 0;

        $packageScope = new PackageScope($this->getModel()->getTable());

        return $this->getModel()->newQuery()
            ->addScope($packageScope)
            ->orderByDesc('payment_gateway.id')
            ->simplePaginate($limit);
    }

    public function updateGateway(User $context, int $id, array $attributes): Gateway
    {
        policy_authorize(GatewayPolicy::class, 'update', $context);

        $gateway = $this->find($id);
        $gateway->update($attributes);
        $gateway->refresh();

        return $gateway;
    }

    public function updateActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(GatewayPolicy::class, 'update', $context);
        $gateway = $this->find($id);

        return $gateway->update(['is_active' => $isActive]);
    }

    public function updateTestMode(User $context, int $id, int $isTestMode): bool
    {
        policy_authorize(GatewayPolicy::class, 'update', $context);

        $gateway = $this->find($id);

        return $gateway->update(['is_test' => $isTestMode]);
    }

    public function getActiveGateways(): Collection
    {
        return $this->getModel()->newQuery()
            ->with(['filters'])
            ->join('packages', function (JoinClause $joinClause) {
                $joinClause->on('packages.alias', '=', 'payment_gateway.module_id')
                    ->where('packages.is_active', '=', 1)
                    ->where('packages.is_installed', '=', 1);
            })
            ->where('payment_gateway.is_active', '=', 1)
            ->get(['payment_gateway.*']);
    }

    /**
     * @inheritDoc
     */
    public function getGatewaysForForm(User $context, array $params = []): Collection
    {
        $gateways = $this->getActiveGateways();

        return collect($gateways)
            ->filter(function (Gateway $gateway) use ($context, $params) {
                return $gateway->getService()->hasAccess($context, $params);
            })->sort(function (Gateway $a, Gateway $b) {
                $aTitle = strlen($a->service);
                $bTitle = strlen($b->service);
                if ($aTitle == $bTitle) {
                    return 0;
                }

                return ($aTitle < $bTitle) ? -1 : 1;
            });
    }

    public function getGatewayByService(string $service): ?Gateway
    {
        return $this->getModel()->newModelQuery()
            ->where('service', '=', $service)
            ->first();
    }

    public function getConfigurationGateways(): Collection
    {
        $services = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass(Constants::DRIVER_TYPE_USER_GATEWAY_FORM);

        if (!count($services)) {
            return collect();
        }

        $services = array_map(function ($service) {
            return str_replace('.gateway.user_form', '', $service);
        }, $services);

        return $this->getModel()->newModelQuery()
            ->whereIn('service', $services)
            ->where('is_active', '=', 1)
            ->get();
    }

    public function setupPaymentGateways(array $configs = []): void
    {
        foreach ($configs as $config) {
            try {
                $gateway = Gateway::query()->getModel()
                    ->where('service', '=', $config['service'])
                    ->first();

                if (!$gateway instanceof Gateway) {
                    $gateway = Gateway::query()->create($config);

                    $gateway->save();
                }

                if (!$gateway instanceof Gateway) {
                    continue;
                }

                $updates = [];

                if (empty($gateway->module_id) || $gateway->module_id !== Arr::get($config, 'module_id')) {
                    Arr::set($updates, 'module_id', Arr::get($config, 'module_id'));
                }

                if (empty($gateway->service_class) || $gateway->service_class !== Arr::get($config, 'service_class')) {
                    Arr::set($updates, 'service_class', Arr::get($config, 'service_class'));
                }

                if (count($updates)) {
                    $gateway->update($updates);
                }

                $this->addFilters($gateway, $config);
            } catch (Exception $e) {
                // silent
            }
        }
    }

    /**
     * @param Gateway       $gateway
     * @param array<string> $params
     *
     * @return void
     */
    protected function addFilters(Gateway $gateway, array $params): void
    {
        $ids         = [];
        $filtersData = Arr::get($params, 'filters', []);

        if (!is_array($filtersData)) {
            return;
        }

        foreach ($filtersData as $entity) {
            $filter = GatewayFilter::query()->firstOrCreate(['entity_type' => $entity]);

            if (!$filter instanceof GatewayFilter) {
                continue;
            }

            $ids[] = $filter->entityId();
        }

        $gateway->filters()->sync($ids);
    }

    public function getGatewaySearchOptions(): array
    {
        return $this->getModel()->newQuery()
            ->get()
            ->map(function ($gateway) {
                return [
                    'label' => $gateway->title,
                    'value' => $gateway->entityId(),
                ];
            })
            ->toArray();
    }

    public function getBuyerConfigurableGateways(): Collection
    {
        return $this->getActiveGateways()
            ->filter(function (Gateway $gateway) {
                if (true !== $gateway->enable_buyer_config) {
                    return false;
                }

                if (!$gateway->getService()->hasBuyerConfigurationAccess()) {
                    return false;
                }

                return true;
            })
            ->values();
    }
}
