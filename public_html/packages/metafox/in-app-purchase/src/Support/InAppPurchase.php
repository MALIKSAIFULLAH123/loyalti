<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Support;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MetaFox\Form\Builder;
use MetaFox\InAppPurchase\Contracts\InAppPurchaseInterface;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Repositories\OrderRepositoryInterface as IapOrderRepositoryInterface;
use MetaFox\InAppPurchase\Support\Providers\Apple;
use MetaFox\InAppPurchase\Support\Providers\Google;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Yup\Yup;
use RuntimeException;

class InAppPurchase implements InAppPurchaseInterface
{
    protected Apple $appleProvider;
    protected Google $googleProvider;

    public function __construct()
    {
        $this->appleProvider  = new Apple();
        $this->googleProvider = new Google();
    }

    private function getDefaultTypes(): array
    {
        return Cache::rememberForever(
            __METHOD__,
            function () {
                $productTypes = [];
                $appTypes     = app('events')->dispatch('in-app-purchase.get_product_type');
                $validator    = function ($item) {
                    return Validator::make($item, [
                        'package_id' => ['required', 'string'],
                        'value'      => ['required', 'string'],
                        'url'        => ['required', 'string'],
                        'label'      => ['required', 'string'],
                    ]);
                };
                if (!is_array($appTypes)) {
                    return $productTypes;
                }
                foreach ($appTypes as $type) {
                    if (!is_array($type)) {
                        continue;
                    }
                    if (array_is_list($type)) {
                        foreach ($type as $item) {
                            if (!$validator($item)->fails()) {
                                $productTypes[] = $item;
                            }
                        }
                        continue;
                    }
                    if (!$validator($type)->fails()) {
                        $productTypes[] = $type;
                    }
                }

                return $productTypes;
            }
        );
    }

    public function getProductTypes(bool $toForm = true, bool $includeHidden = false): array
    {
        $options = [];
        foreach ($this->getDefaultTypes() as $productType) {
            if (!Arr::exists($productType, 'package_id')
                || !app_active($productType['package_id'])) {
                continue;
            }
            if (!$includeHidden && !empty($productType['hidden'])) {
                continue;
            }
            if ($toForm) {
                unset($productType['package_id']);
            }
            $options[] = $productType;
        }

        return $options;
    }

    public function getProductTypeByValue(string $value): ?array
    {
        $type = array_filter($this->getDefaultTypes(), function ($type) use ($value) {
            return $type['value'] == $value;
        });

        return array_shift($type);
    }

    /**
     * @throws Exception
     */
    public function handleCallback(string $platform, array $data): bool
    {
        try {
            Log::channel('payment')->info('Handle In-app Webhook: ' . $platform, $data);
            if (!in_array($platform, [Constants::IOS, Constants::ANDROID])) {
                return false;
            }

            return match ($platform) {
                Constants::IOS     => $this->appleProvider->handleCallback($data),
                Constants::ANDROID => $this->googleProvider->handleCallback($data),
            };
        } catch (Exception) {
            return false;
        }
    }

    public function getSettingFormFields(): array
    {
        return [
            Builder::switch('in-app-purchase.enable_iap_sandbox_mode')
                ->label(__p('in-app-purchase::admin.enable_iap_sandbox_mode'))
                ->description(__p('in-app-purchase::admin.enable_iap_sandbox_mode_description')),
            Builder::switch('in-app-purchase.enable_iap_ios')
                ->label(__p('in-app-purchase::admin.enable_iap_ios'))
                ->description(__p(
                    'in-app-purchase::admin.enable_iap_ios_description',
                    [
                        'guideLink'  => 'https://help.apple.com/app-store-connect/#/dev0067a330b',
                        'link'       => apiUrl('in-app-purchase.callback', ['platform' => Constants::IOS], true),
                        'apple_link' => 'https://developer.apple.com/help/app-store-connect/configure-in-app-purchase-settings/generate-keys-for-in-app-purchases/',
                    ]
                )),
            Builder::text('in-app-purchase.apple_app_id')
                ->label(__p('in-app-purchase::admin.apple_app_id'))
                ->description(__p('in-app-purchase::admin.apple_app_id_description'))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_ios'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_ios')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
            Builder::text('in-app-purchase.apple_issuer_id')
                ->label(__p('in-app-purchase::admin.apple_issuer_id'))
                ->description(__p(
                    'in-app-purchase::admin.apple_issuer_id_description',
                    ['link' => 'https://developer.apple.com/help/app-store-connect/configure-in-app-purchase-settings/generate-keys-for-in-app-purchases/']
                ))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_ios'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_ios')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
            Builder::text('in-app-purchase.apple_key_id')
                ->label(__p('in-app-purchase::admin.apple_key_id'))
                ->description(__p('in-app-purchase::admin.apple_key_id_description'))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_ios'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_ios')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
            Builder::textArea('in-app-purchase.apple_private_key')
                ->label(__p('in-app-purchase::admin.apple_private_key'))
                ->description(__p('in-app-purchase::admin.apple_private_key_description'))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_ios'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_ios')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
            Builder::text('in-app-purchase.apple_bundle_id')
                ->label(__p('in-app-purchase::admin.apple_bundle_id'))
                ->description(__p('in-app-purchase::admin.apple_bundle_id_description'))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_ios'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_ios')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
            Builder::switch('in-app-purchase.enable_iap_android')
                ->label(__p('in-app-purchase::admin.enable_iap_android'))
                ->description(__p(
                    'in-app-purchase::admin.enable_iap_android_description',
                    [
                        'link'         => '/admincp/in-app-purchase/setting/google-service-account',
                        'android_link' => 'https://developer.android.com/google/play/billing/getting-ready#configure-rtdn',
                        'webhook'      => apiUrl('in-app-purchase.callback', ['platform' => Constants::ANDROID], true),
                    ]
                )),
            Builder::text('in-app-purchase.google_android_package_name')
                ->label(__p('in-app-purchase::admin.google_android_package_name'))
                ->description(__p('in-app-purchase::admin.google_android_package_name_description'))
                ->returnKeyType('next')
                ->showWhen(['truthy', 'in-app-purchase.enable_iap_android'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('enable_iap_android')
                                ->is('$exists')
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                ),
        ];
    }

    /**
     * @throws Exception
     */
    public function verifyReceipt(array $data, User $context): bool
    {
        $platform      = Arr::get($data, 'platform');
        $transactionId = Arr::get($data, 'transaction_id', '');
        $purchaseToken = Arr::get($data, 'purchase_token');

        $transaction = match ($platform) {
            'ios'     => $this->appleProvider->verifyTransaction($transactionId),
            'android' => $this->googleProvider->verifyToken(Arr::get($data, 'subscription_id'), $purchaseToken)
        };

        if (!$transaction) {
            throw new RuntimeException(__p('in-app-purchase::phrase.invalid_in_app_purchase'));
        }

        if (Arr::get($transaction, 'status') !== Transaction::STATUS_COMPLETED) {
            throw new RuntimeException('E_TRANSACTION_EXPIRED');
        }

        if (!$this->validateBuyers($context, $transaction, $platform)) {
            return false;
        }

        $order = $this->retrieveOrCreateOrder($data, $transaction, $context);

        $order->gateway_subscription_id = Arr::get($transaction, 'gateway_subscription_id');
        $order->gateway_order_id        = Arr::get($transaction, 'gateway_order_id');
        $order->save();

        $order->refresh();

        $isRecurring = Arr::get($transaction, 'is_recurring');

        Payment::onPaymentSuccess($order, $transaction, $transaction);

        if ($isRecurring) {
            Payment::onSubscriptionActivated($order, [
                'gateway_subscription_id' => Arr::get($transaction, 'gateway_order_id'),
                'amount'                  => Arr::get($transaction, 'amount'),
            ]);
        }

        return true;
    }

    protected function validateBuyers(User $context, array $transaction, string $platform): bool
    {
        $orgTransactionId = $transaction['gateway_order_id'] ?? $transaction['gateway_subscription_id'];
        $transactionId    = $transaction['id'];
        $gateway          = Payment::getManager()->getGatewayByName('in-app-purchase');

        $iapOrderSameTransaction = $this->getIapOrderRepository()->getOrderByPlatform($platform, $orgTransactionId, $transactionId);

        if ($iapOrderSameTransaction) {
            if ($iapOrderSameTransaction->user_id == $context->entityId()) {
                throw new RuntimeException('E_ITEM_DUPLICATED');
            }
            throw new RuntimeException('E_ITEM_NOT_OWNED');
        }

        if (!$transaction['is_recurring']) {
            return true;
        }

        $orderTransaction =  $this->getOrderRepository()->getModel()
                                ->newModelQuery()
                                ->where([
                                    'gateway_subscription_id' => $orgTransactionId,
                                    'gateway_id'              => $gateway->id,
                                ])->orderBy('created_at', 'desc')->first();

        if (!$orderTransaction) {
            return true;
        }

        $inAppOrder   = $this->getIapOrderRepository()->getOrderByPlatform($platform, $orgTransactionId);
        $inAppProduct = $this->getProductRepository()->getProductByStoreId(Arr::get($transaction, 'product_id'), $platform);

        if (!$inAppProduct) {
            throw new RuntimeException(__p('in-app-purchase::phrase.invalid_in_app_purchase'));
        }

        if ($inAppOrder && $inAppOrder->product_id != $inAppProduct->id) {
            return true;
        }

        if ($orderTransaction->userId() == $context->entityId()
            && $orderTransaction->recurring_status == Order::RECURRING_STATUS_ACTIVE) {
            throw new RuntimeException('E_ITEM_DUPLICATED');
        }

        if ($orderTransaction->userId() != $context->entityId()
            && !in_array($orderTransaction->recurring_status, [Order::RECURRING_STATUS_ENDED, Order::RECURRING_STATUS_CANCELLED])) {
            throw new RuntimeException('E_ITEM_NOT_OWNED');
        }

        return true;
    }
    public function retrieveOrCreateOrder(array $data, array $transaction, User $context): ?Order
    {
        /** @var Order $order */
        $orderId  = Arr::get($data, 'gateway_token');
        $itemId   = Arr::get($data, 'item_id');
        $itemType = Arr::get($data, 'item_type');
        $order    = null;
        if ($orderId) {
            $order = $this->getOrderById($orderId);
        }

        if ($itemId && $itemType) {
            $this->deletePendingOrderByItem($itemId, $itemType);
        }

        if (!$order) {
            $order = $this->createOrder($data, $transaction, $context);
        }

        $this->createIapOrder($data, $transaction, $context, $order->id);

        return $order;
    }

    protected function createIapOrder(array $data, array $transaction, User $context, int $orderId): void
    {
        if (!$orderId) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        $productId    = Arr::get($transaction, 'product_id');
        $platform     = Arr::get($data, 'platform');
        $inAppProduct = $this->getProductRepository()->getProductByStoreId($productId, $platform);
        $isRecurring  = Arr::get($transaction, 'is_recurring');
        $attributes   = [
            'platform'         => $platform,
            'product_id'       => $inAppProduct->entityId(),
            'payment_order_id' => $orderId,
            'transaction_id'   => Arr::get($transaction, 'id'),
            'is_recurring'     => $isRecurring,
            'expires_at'       => Carbon::createFromTimestamp(Arr::get($transaction, 'expires_at'))->toIso8601String(),
        ];
        $gatewayTranId = !$isRecurring ? Arr::get($transaction, 'gateway_order_id') : Arr::get($transaction, 'gateway_subscription_id');

        if ($platform == Constants::IOS) {
            $attributes['original_transaction_id'] = $gatewayTranId;
        } else {
            $attributes['purchase_token'] = $gatewayTranId;
        }

        $this->getIapOrderRepository()->createIapOrder($context, $attributes);
    }
    protected function getOrderById(int $orderId): ?Order
    {
        $order = $this->getOrderRepository()->find($orderId);

        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        return $order;
    }
    protected function deletePendingOrderByItem(int $itemId, string $itemType): void
    {
        $order = $this->getOrderRepository()->getModel()
            ->newModelQuery()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])->first();

        if (!$order instanceof Order) {
            return;
        }
        if ($order->status != Order::STATUS_INIT) {
            return;
        }
        $modelClass = Relation::getMorphedModel($order->item_type);

        if ($modelClass) {
            /** @var Model $modelInstance */
            $modelInstance = resolve($modelClass);

            $model = $modelInstance->newModelQuery()->where([
                'id' => $order->item_id,
            ])->first();

            if ($model instanceof $modelInstance) {
                $model->delete();
            }
        }
        $order->delete();
    }

    protected function createOrder(array $data, array $transaction, User $context): ?Order
    {
        $productId    = Arr::get($transaction, 'product_id');
        $platform     = Arr::get($data, 'platform');
        $inAppProduct = $this->getProductRepository()->getProductByStoreId($productId, $platform);
        $gateway      = Payment::getManager()->getGatewayByName('in-app-purchase');

        if (!$gateway instanceof Gateway) {
            throw new RuntimeException(__p('in-app-purchase::phrase.in_app_purchase_is_not_available'));
        }

        if (!$inAppProduct) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_product_cannot_found'));
        }

        $params = [
            'renew_type'      => 'auto',
            'force_create'    => true,
            'payment_gateway' => $gateway->id,
            'id'              => $inAppProduct->item_id,
        ];

        $invoice = app('events')->dispatch('in-app-purchase.create_invoice', [$inAppProduct->item_type, $context, $params], true);

        if (!$invoice) {
            throw new RuntimeException(__p('in-app-purchase::phrase.init_order_failed'));
        }
        $orderId     = (int) Arr::get($invoice, 'gateway_token', 0);

        return $this->getOrderById($orderId);
    }

    public function getOrderRepository(): OrderRepositoryInterface
    {
        return resolve(OrderRepositoryInterface::class);
    }

    public function getIapOrderRepository(): IapOrderRepositoryInterface
    {
        return resolve(IapOrderRepositoryInterface::class);
    }

    public function getProductRepository(): ProductRepositoryInterface
    {
        return resolve(ProductRepositoryInterface::class);
    }
}
