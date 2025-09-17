<?php

namespace MetaFox\InAppPurchase\Repositories\Eloquent;

use Illuminate\Support\Carbon;
use MetaFox\InAppPurchase\Support\Constants;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\InAppPurchase\Repositories\OrderRepositoryInterface;
use MetaFox\InAppPurchase\Models\Order;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class OrderRepository.
 * @method Order getModel()
 */
class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public function model()
    {
        return Order::class;
    }

    public function createIapOrder(ContractUser $context, array $attributes): Order
    {
        $attributes = array_merge($attributes, [
            'user_id'   => $context->userId(),
            'user_type' => $context->userType(),
        ]);

        $order = new Order($attributes);

        $order->save();

        $order->refresh();

        return $order;
    }

    public function getOrderByPlatform(string $platform, string $orgTransactionId, ?string $transactionId = null): ?Order
    {
        $where = [
            'platform' => $platform,
        ];

        match ($platform) {
            Constants::IOS     => $where['original_transaction_id'] = $orgTransactionId,
            Constants::ANDROID => $where['purchase_token']          = $orgTransactionId
        };

        if ($transactionId) {
            $where['transaction_id'] = $transactionId;
        }

        return $this->getModel()->newModelQuery()
            ->where($where)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
