<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Support\Providers;

use Exception;
use Google\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\InAppPurchase\Models\Product;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Constants;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Facades\Settings;
use RuntimeException;

class Google extends AbstractProvider
{
    protected function getGoogleAccessToken(): ?string
    {
        try {
            $settingServiceAcc = Settings::get('in-app-purchase.google_service_account_file');

            $serviceAccountFile = app('storage')->disk('local')->path($settingServiceAcc);
            $client             = new Client();
            $client->setAuthConfig($serviceAccountFile);
            $client->setScopes(['https://www.googleapis.com/auth/androidpublisher']);
            $client->fetchAccessTokenWithAssertion();

            return $client->getAccessToken()['access_token'] ?? null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    public function verifyToken(string $subscriptionId, string $token, ?Product $product = null): ?array
    {
        if (!$subscriptionId || !$token) {
            return null;
        }

        if (!$product) {
            $product = $this->getProductRepository()->getProductByStoreId($subscriptionId, Constants::ANDROID);
        }

        if (!$product) {
            return null;
        }
        $androidPackageName = Settings::get('in-app-purchase.google_android_package_name');
        $accessToken        = $this->getGoogleAccessToken();
        if (!$accessToken) {
            return null;
        }
        $type = $product->is_recurring ? 'subscriptions' : 'products';
        $url  = Constants::GOOGLE_ANDROID_PUBLISHER_URL . "/$androidPackageName/purchases/$type/$subscriptionId/tokens/$token";

        $headers = [
            'Accept: application/json',
            "Authorization: Bearer $accessToken",
        ];

        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curlHandle);

        curl_close($curlHandle);
        $response = json_decode($response, true);
        if (Arr::get($response, 'error')) {
            throw new Exception(Arr::get($response, 'error.message'), Arr::get($response, 'error.code'));
        }

        return [
            'product_id'              => $subscriptionId,
            'gateway_order_id'        => $product->is_recurring ? null : $token,
            'gateway_subscription_id' => $product->is_recurring ? $token : null,
            'id'                      => Arr::get($response, 'orderId'),
            'user_id'                 => Arr::get($response, 'obfuscatedExternalAccountId'),
            'amount'                  => Arr::get($response, 'priceAmountMicros') / 1000000,
            'currency'                => Arr::get($response, 'priceCurrencyCode'),
            'expires_at'              => $product->is_recurring ? Arr::get($response, 'expiresDate') / 1000 : null,
            'is_recurring'            => (bool) $product->is_recurring,
            'raw_data'                => $response,
            'status'                  => $this->detectGoogleSubscriptionStatus($response),
        ];
    }

    private function detectGoogleSubscriptionStatus(array $response): string
    {
        $expiryTime   = Arr::get($response, 'expiryTimeMillis') / 1000;
        $paymentState = Arr::get($response, 'paymentState');

        if ($expiryTime > time()) {
            return Transaction::STATUS_COMPLETED;
        }

        if ($paymentState === 0) {
            return Transaction::STATUS_PENDING;
        }

        return Transaction::STATUS_FAILED;
    }

    /**
     * @throws Exception
     */
    public function handleCallback(array $data): bool
    {
        // 1. Get message.data
        $messageData = Arr::get($data, 'message.data');

        if (!$messageData) {
            return false;
        }
        // 2. Get subscription notification
        $decodedData = base64_decode($messageData);
        if (!$decodedData) {
            return false;
        }
        $notification        = json_decode($decodedData, true);
        $subNotification     = Arr::get($notification, 'subscriptionNotification');
        $oneTimeNotification = Arr::get($notification, 'oneTimeProductNotification');

        if (!$subNotification && !$oneTimeNotification) {
            Log::channel('payment')->error('Handle In-app Webhook: android - Invalid payload');

            return false;
        }
        if ($subNotification) {
            return $this->handleSubscriptionCallback($subNotification);
        }

        return $this->handleOneTimeProductCallback($oneTimeNotification);
    }

    /**
     * @throws Exception
     */
    protected function handleSubscriptionCallback(array $payload): bool
    {
        $notificationType = Arr::get($payload, 'notificationType');
        $purchaseToken    = Arr::get($payload, 'purchaseToken');
        $subscriptionId   = Arr::get($payload, 'subscriptionId');

        $product = $this->getProductRepository()->getProductByStoreId($subscriptionId, Constants::ANDROID);
        if (!$product) {
            return false;
        }

        $purchaseData = $this->verifyToken($subscriptionId, $purchaseToken);

        if (!$purchaseData) {
            return false;
        }

        return match ((int) $notificationType) {
            // (3) SUBSCRIPTION_CANCELED or (12) SUBSCRIPTION_REVOKED or (10) SUBSCRIPTION_PAUSED
            3, 10, 12 => $this->onCancelled($purchaseData),
            // (13) SUBSCRIPTION_EXPIRED
            13 => $this->onExpired($purchaseData),
            // (1) SUBSCRIPTION_RECOVERED or (4) SUBSCRIPTION_PURCHASED or (7) SUBSCRIPTION_RESTARTED
            1, 4, 7 => $this->onRecurringSuccess($purchaseData),
            // (2) SUBSCRIPTION_RENEWED
            2       => $this->onRenewal($purchaseData),
            default => false
        };
    }

    /**
     * @throws Exception
     */
    protected function handleOneTimeProductCallback(array $payload): bool
    {
        $notificationType = Arr::get($payload, 'notificationType');
        $purchaseToken    = Arr::get($payload, 'purchaseToken');
        $productId        = Arr::get($payload, 'sku');

        $product = $this->getProductRepository()->getProductByStoreId($productId, Constants::ANDROID);
        if (!$product) {
            return false;
        }

        $purchaseData = $this->verifyToken($productId, $purchaseToken);

        if (!$purchaseData) {
            return false;
        }

        return match ($notificationType) {
            // (1) ONE_TIME_PRODUCT_PURCHASED
            '1' => $this->onPaymentSuccess($purchaseData),
        };
    }

    private function onPaymentSuccess(array $data): bool
    {
        $order = $this->getOrderRepository()->getByGatewayOrderId($data['gateway_order_id'], $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $transaction = [
            'id'       => $data['id'],
            'currency' => $data['currency'],
            'amount'   => $data['amount'],
            'status'   => Transaction::STATUS_COMPLETED,
            'raw_data' => $data['raw_data'],
        ];

        Payment::onPaymentSuccess($order, $transaction, $data);

        return true;
    }

    private function onRecurringSuccess(array $data): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($data['gateway_subscription_id'], $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $transaction = [
            'id'       => $data['id'],
            'currency' => $data['currency'],
            'amount'   => $data['amount'],
            'status'   => Transaction::STATUS_COMPLETED,
            'raw_data' => $data['raw_data'],
        ];

        Payment::onPaymentSuccess($order, $transaction, $data);

        Payment::onSubscriptionActivated($order, [
            'gateway_subscription_id' => Arr::get($transaction, 'gateway_order_id'),
            'amount'                  => Arr::get($transaction, 'amount'),
        ]);

        return true;
    }

    private function onExpired(array $data): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($data['gateway_subscription_id'], $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        Payment::onSubscriptionExpired($order, [
            'gateway_subscription_id' => $data['id'],
            'amount'                  => $data['amount'],
        ]);

        return true;
    }

    private function onCancelled(array $data): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($data['gateway_subscription_id'], $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        Payment::onSubscriptionCancelled($order, [
            'gateway_subscription_id' => $data['id'],
            'amount'                  => $data['amount'],
        ]);

        return true;
    }

    private function onRenewal(array $data): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($data['gateway_subscription_id'], $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $transaction = [
            'id'       => $data['id'],
            'currency' => $data['currency'],
            'amount'   => $data['amount'],
            'status'   => Transaction::STATUS_COMPLETED,
            'raw_data' => $data,
        ];

        Payment::onSubscriptionRecycled($order, [
            'gateway_subscription_id' => $data['id'],
            'amount'                  => $data['amount'],
        ], $transaction);

        return true;
    }
}
