<?php

namespace MetaFox\Translation\Repositories\Eloquent;

use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Translation\Models\TranslationGateway;
use MetaFox\Translation\Policies\TranslationGatewayPolicy;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;

/**
 * Class TranslationGatewayRepository.
 * @method TranslationGateway getModel()
 * @method TranslationGateway find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class TranslationGatewayRepository extends AbstractRepository implements TranslationGatewayRepositoryInterface
{

    public function model()
    {
        return TranslationGateway::class;
    }

    public function viewTranslationGateways(User $context, array $attributes): Paginator
    {
        policy_authorize(TranslationGatewayPolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'] ?? 0;

        $packageScope = new PackageScope($this->getModel()->getTable());

        return $this->getModel()->newQuery()
            ->addScope($packageScope)
            ->orderByDesc('translation_gateway.id')
            ->simplePaginate($limit);
    }

    public function updateTranslationGateway(User $context, int $id, array $attributes): TranslationGateway
    {
        policy_authorize(TranslationGatewayPolicy::class, 'update', $context);

        $gateway = $this->find($id);
        $gateway->update($attributes);
        $gateway->refresh();

        return $gateway;
    }

    public function updateActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(TranslationGatewayPolicy::class, 'update', $context);
        $gateway = $this->find($id);

        return $gateway->update(['is_active' => $isActive]);
    }

    public function setupTranslationGateways($configs): void
    {
        foreach ($configs as $config) {
            try {
                $gateway = TranslationGateway::query()->getModel()
                    ->where('service', '=', $config['service'])
                    ->first();

                if (empty($gateway)) {
                    $gateway = TranslationGateway::query()->create($config);
                    $gateway->save();
                }

                if (empty($gateway->module_id)) {
                    $gateway->update(['module_id' => $config['module_id']]);
                }

                if (!$gateway instanceof TranslationGateway) {
                    continue;
                }
            } catch (Exception $e) {
                // silent
            }
        }
    }
}
