<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Policies\UserConfigurationPolicy;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Payment\Repositories\UserConfigurationRepositoryInterface;
use MetaFox\Payment\Models\UserConfiguration;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserConfigurationRepository.
 */
class UserConfigurationRepository extends AbstractRepository implements UserConfigurationRepositoryInterface
{
    public function model()
    {
        return UserConfiguration::class;
    }

    public function getConfiguration(int $userId, string $serviceName): ?array
    {
        $configuration = $this->getModel()->newModelQuery()
            ->join('payment_gateway', function (JoinClause $joinClause) use ($serviceName) {
                $joinClause->on('payment_gateway.id', '=', 'payment_user_configurations.gateway_id')
                    ->where('payment_gateway.service', '=', $serviceName);
            })
            ->where([
                'payment_user_configurations.user_id' => $userId,
            ])
            ->first();

        if (null === $configuration) {
            return null;
        }

        if (null === $configuration->value) {
            return null;
        }

        if (!is_array($configuration->value)) {
            return null;
        }

        if (!count($configuration->value)) {
            return null;
        }

        return $configuration->value;
    }

    public function updateConfiguration(int $userId, array $attributes): bool
    {
        $user = resolve(UserRepositoryInterface::class)->find($userId);

        $gatewayId = Arr::get($attributes, 'gateway_id', 0);

        $gateway = resolve(GatewayRepositoryInterface::class)->find($gatewayId);

        $context = user();

        policy_authorize(UserConfigurationPolicy::class, 'update', $context, $user, $gateway);

        $configuration = $this->getModel()->newModelQuery()
            ->where([
                'gateway_id' => $gateway->entityId(),
                'user_id'    => $user->entityId(),
                'user_type'  => $user->entityType(),
            ])
            ->first();

        if (null === $configuration) {
            $configuration = new UserConfiguration();

            $configuration->fill(array_merge($attributes, [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
            ]));

            $configuration->save();

            return true;
        }

        $configuration->fill([
            'value' => Arr::get($attributes, 'value'),
        ]);

        $configuration->save();

        return true;
    }

    public function updateMultipleConfiguration(int $userId, array $attributes): bool
    {
        $serviceKeys = array_keys($attributes);
        if (count($serviceKeys) === 0) {
            return false;
        }

        $gateways = Gateway::query()
            ->whereIn('service', $serviceKeys)
            ->where('is_active', 1)
            ->get();

        if ($gateways->count() === 0) {
            return false;
        }

        foreach ($gateways as $gateway) {
            $payload = Arr::get($attributes, $gateway->service);

            if (!is_array($payload)) {
                continue;
            }

            $payload['gateway_id'] = $gateway->entityId();

            $this->updateConfiguration($userId, $payload);
        }

        return true;
    }

    public function getConfigurationByGatewayId(int $userId, int $gatewayId): ?UserConfiguration
    {
        return $this->getModel()->newModelQuery()
            ->where([
                'user_id'    => $userId,
                'gateway_id' => $gatewayId,
            ])
            ->first();
    }

    public function hasAccess(int $userId, int $gatewayId): bool
    {
        $gateway = resolve(GatewayRepositoryInterface::class)
            ->where([
                'is_active' => 1,
            ])
            ->find($gatewayId);

        $service = $gateway->getService();

        $rules = $service->getFormFieldRules();

        if (!count($rules)) {
            return true;
        }

        $configuration = $this->getConfigurationByGatewayId($userId, $gatewayId);

        if (null === $configuration) {
            return false;
        }

        $values = $configuration->value;

        if (null === $values) {
            return false;
        }

        $validator = Validator::make($values, $rules);

        return $validator->passes();
    }
}
