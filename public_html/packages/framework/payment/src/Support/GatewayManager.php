<?php

namespace MetaFox\Payment\Support;

use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;
use MetaFox\Payment\Contracts\GatewayInterface;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\User;

/**
 * Class Payment.
 */
class GatewayManager implements GatewayManagerInterface
{
    public GatewayRepositoryInterface $gatewayRepository;

    public function __construct(GatewayRepositoryInterface $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    /**
     * @return Collection
     */
    public function getActiveGateways(): Collection
    {
        return $this->gatewayRepository->getActiveGateways();
    }

    public function getGatewayById(int $gatewayId): ?Gateway
    {
        return Gateway::find($gatewayId);
    }

    public function getActiveGatewayById(int $gatewayId): ?Gateway
    {
        return Gateway::query()
            ->join('packages', function (JoinClause $joinClause) {
                $joinClause->on('payment_gateway.module_id', '=', 'packages.alias')
                    ->where('packages.is_active', '=', 1)
                    ->where('packages.is_installed', '=', 1);
            })
            ->where('payment_gateway.id', '=', $gatewayId)
            ->first(['payment_gateway.*']);
    }

    public function getGatewayByName(string $gatewayName): ?Gateway
    {
        return Gateway::firstWhere('service', $gatewayName);
    }

    public function getGatewayServiceById(int $gatewayId): GatewayInterface
    {
        try {
            $gateway = $this->getGatewayById($gatewayId);

            if (!$gateway instanceof Gateway) {
                throw new ItemNotFoundException('Payment gateway not found');
            }

            return $gateway->getService();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getActiveGatewayServiceById(int $gatewayId): GatewayInterface
    {
        try {
            $gateway = $this->getActiveGatewayById($gatewayId);

            if (!$gateway instanceof Gateway) {
                throw new ItemNotFoundException('Payment gateway not found');
            }

            return $gateway->getService();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getGatewayServiceByName(string $gatewayName): GatewayInterface
    {
        try {
            $gateway = $this->getGatewayByName($gatewayName);
            if (!$gateway instanceof Gateway) {
                throw new ItemNotFoundException('Payment gateway not found');
            }

            return $gateway->getService();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getGatewaysForForm(User $context, array $params = [], ?Entity $resource = null): array
    {
        $options = $this->gatewayRepository->getGatewaysForForm($context, $params);

        $fields         = [];
        $params['item'] = $this->predictGatewayItem($context, $resource);
        $userCurrency   = Arr::get($params, 'currency_id', app('currency')->getUserCurrencyId($context));

        foreach ($options as $gateway) {
            if (!$gateway instanceof Gateway) {
                continue;
            }

            $service = $gateway->getService();

            if ($userCurrency && !$service->isSupportedCurrency($userCurrency)) {
                continue;
            }

            $gatewayFields = $service->getCheckoutButton($context, $params);

            if (empty($gatewayFields)) {
                continue;
            }

            $fields = array_merge($fields, Arr::wrap($gatewayFields));
        }

        return $fields;
    }

    /**
     * @param User        $context
     * @param Entity|null $resource
     *
     * @return array
     */
    protected function predictGatewayItem(User $context, ?Entity $resource = null): array
    {
        if ($resource instanceof Entity) {
            [$price, $currency] = $this->getResourcePrice($context, $resource);

            return [
                'price'         => $price,
                'currency'      => Str::lower($currency),
                'title'         => $resource instanceof HasTitle || method_exists($resource, 'toTitle') ? $resource->toTitle() : 'Item #' . $resource->entityId(),
                'resource_name' => $resource->entityType(),
            ];
        }

        return [];
    }

    /**
     * @param User   $context
     * @param Entity $resource
     *
     * @return array
     */
    private function getResourcePrice(User $context, Entity $resource): array
    {
        $price    = 0;
        $currency = '';
        // IsBillable item
        if ($resource instanceof IsBillable) {
            $order = $resource->toOrder();
        }
        // Resource with toPayment
        if (method_exists($resource, 'toPayment')) {
            $order = call_user_func([$resource, 'toPayment'], $context);
        }

        if (isset($order) && is_array($order)) {
            $price    = Arr::get($order, 'price') ?? Arr::get($order, 'total');
            $currency = Arr::get($order, 'currency') ?? Arr::get($order, 'currency_id');
        } else {
            // Check property
            $prices = $resource->price ?? $resource->initial_price ?? [];
            if (is_string($prices)) {
                $prices = json_decode($prices, true);
            }
            if (json_last_error() == JSON_ERROR_NONE) {
                $currency = $resource->currency ?? app('currency')->getUserCurrencyId($context);
                $price    = is_array($prices) ? Arr::get($prices, $currency) : $prices;
            }
        }

        return [$price, $currency];
    }
}
