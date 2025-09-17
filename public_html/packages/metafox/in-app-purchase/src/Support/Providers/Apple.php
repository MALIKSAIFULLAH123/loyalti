<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Support\Providers;

use AppStoreServerLibrary\AppStoreServerAPIClient;
use AppStoreServerLibrary\Models\Environment;
use AppStoreServerLibrary\AppStoreServerAPIClient\APIException;
use AppStoreServerLibrary\Models\JWSTransactionDecodedPayload;
use AppStoreServerLibrary\SignedDataVerifier;
use AppStoreServerLibrary\SignedDataVerifier\VerificationException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\InAppPurchase\Support\Constants;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Facades\Settings;
use RuntimeException;

class Apple extends AbstractProvider
{
    protected function loadStoreServerApiClient(): ?AppStoreServerAPIClient
    {
        $keyId      = Settings::get('in-app-purchase.apple_key_id');
        $issuerId   = Settings::get('in-app-purchase.apple_issuer_id');
        $bundleId   = Settings::get('in-app-purchase.apple_bundle_id');
        $privateKey = Settings::get('in-app-purchase.apple_private_key');
        $sandbox    = Settings::get('in-app-purchase.enable_iap_sandbox_mode');

        if (!$keyId || !$issuerId || !$bundleId || !$privateKey) {
            return null;
        }

        return new AppStoreServerAPIClient(
            signingKey: $privateKey,
            keyId: $keyId,
            issuerId: $issuerId,
            bundleId: $bundleId,
            environment: $sandbox ? Environment::SANDBOX : Environment::PRODUCTION
        );
    }

    /**
     * @throws VerificationException
     */
    protected function loadSignedDataVerifier(): ?SignedDataVerifier
    {
        $appId      = Settings::get('in-app-purchase.apple_app_id');
        $keyId      = Settings::get('in-app-purchase.apple_key_id');
        $issuerId   = Settings::get('in-app-purchase.apple_issuer_id');
        $bundleId   = Settings::get('in-app-purchase.apple_bundle_id');
        $privateKey = Settings::get('in-app-purchase.apple_private_key');
        $sandbox    = Settings::get('in-app-purchase.enable_iap_sandbox_mode');

        if (!$keyId || !$issuerId || !$bundleId || !$privateKey || !$appId) {
            return null;
        }
        $rootCertificatesPath = [
            'assets/in-app-purchase/certs/AppleComputerRootCertificate.cer',
            'assets/in-app-purchase/certs/AppleIncRootCertificate.cer',
            'assets/in-app-purchase/certs/AppleRootCA-G2.cer',
            'assets/in-app-purchase/certs/AppleRootCA-G3.cer',
        ];
        $rootCertsBytes = [];
        foreach ($rootCertificatesPath as $certPath) {
            $certPath = app('storage')->disk('asset')->path($certPath);
            if (!file_exists($certPath)) {
                return null;
            }

            $certBytes = file_get_contents($certPath);

            if ($certBytes === false) {
                return null;
            }

            $rootCertsBytes[] = $certBytes;
        }

        return new SignedDataVerifier(
            rootCertificates: $rootCertsBytes,
            enableOnlineChecks: true,
            environment: $sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
            bundleId: $bundleId,
            appAppleId: $sandbox ? null : $appId
        );
    }

    public function verifyTransaction(string $transactionId): ?array
    {
        try {
            if (!$transactionId) {
                return null;
            }

            $client = $this->loadStoreServerApiClient();

            if (!$client) {
                return null;
            }

            $response              = $client->getTransactionInfo($transactionId);
            $signedTranInfo        = $response->getSignedTransactionInfo();

            if (!$signedTranInfo) {
                return null;
            }

            [, $payload]           = explode('.', $signedTranInfo);
            $transactionInfo       = json_decode(base64_decode($payload), true);
            $originalTranId        = Arr::get($transactionInfo, 'originalTransactionId');
            $appAccountToken       = Arr::get($transactionInfo, 'appAccountToken');
            if ($appAccountToken) {
                $explodeToken    = explode('-', $appAccountToken);
                $appAccountToken = ltrim(end($explodeToken), '0');
            }
            $type                  = Arr::get($transactionInfo, 'type');
            $isRecurring           = $type == Constants::APPLE_AUTO_RENEWABLE_SUBSCRIPTION;

            return [
                'product_id'              => Arr::get($transactionInfo, 'productId'),
                'gateway_order_id'        => $isRecurring ? null : $originalTranId,
                'gateway_subscription_id' => $isRecurring ? $originalTranId : null,
                'id'                      => Arr::get($transactionInfo, 'transactionId'),
                'user_id'                 => $appAccountToken,
                'amount'                  => Arr::get($transactionInfo, 'price') / 1000,
                'currency'                => Arr::get($transactionInfo, 'currency'),
                'expires_at'              => $isRecurring ? Arr::get($transactionInfo, 'expiresDate') / 1000 : null,
                'is_recurring'            => $isRecurring,
                'raw_data'                => $transactionInfo,
                'status'                  => $this->detectAppleSubscriptionStatus($transactionInfo),
            ];
        } catch (APIException $e) {
            throw new Exception($e->getErrorMessage());
        }
    }

    private function detectAppleSubscriptionStatus(array $transactionInfo): string
    {
        $expiresDate = Arr::get($transactionInfo, 'expiresDate') / 1000;
        $isUpgrade   = Arr::get($transactionInfo, 'isUpgraded');

        if ($expiresDate > time() || $isUpgrade) {
            return Transaction::STATUS_COMPLETED;
        }

        return Transaction::STATUS_FAILED;
    }
    /**
     * @throws VerificationException
     */
    public function handleCallback(array $data): bool
    {
        try {
            // Get Gateway
            if (!$this->gateway) {
                Log::channel('payment')->error('Invalid Apple in-app webhook: missing gateway');

                return false;
            }

            // 1. Get signed payload
            $signedPayload = Arr::get($data, 'signedPayload');
            if (!$signedPayload) {
                Log::channel('payment')->error('Invalid Apple in-app webhook: missing signedPayload');

                return false;
            }
            // 2. Validate payloads
            $signedDataVerifier = $this->loadSignedDataVerifier();
            if (!$signedDataVerifier) {
                Log::channel('payment')->error('Invalid Apple in-app webhook: error when load signedDataVerifier');

                return false;
            }
            $notification = $signedDataVerifier->verifyAndDecodeNotification($signedPayload);

            // 3. Check notification type
            $notificationType    = $notification->getNotificationType()?->value;
            $subNotificationType = $notification->getSubtype()?->value;
            if ($notificationType == 'DID_CHANGE_RENEWAL_PREF' && $subNotificationType != 'UPGRADE') {
                return true; // User downgrade subscription, no need to handle now
            }

            // 4. Check transaction info
            $signedTranInfo        = $notification->getData()->getSignedTransactionInfo();
            $signedTransaction     = $signedDataVerifier->verifyAndDecodeSignedTransaction($signedTranInfo);

            return match ($notificationType) {
                'DID_CHANGE_RENEWAL_PREF', 'REFUND_REVERSED', 'SUBSCRIBED' => $this->onPaymentSuccess($signedTransaction),
                'DID_RENEW' => $this->onRenewal($signedTransaction),
                'REVOKE', 'REFUND' => $this->onCancelled($signedTransaction),
                'EXPIRED' => $this->onExpired($signedTransaction),
                default   => false
            };
        } catch (Exception $e) {
            Log::channel('payment')->error('Invalid Apple in-app webhook: ' . $e->getMessage());

            return false;
        }
    }

    private function onPaymentSuccess(JWSTransactionDecodedPayload $payload): bool
    {
        if ($payload->getType() == Constants::APPLE_AUTO_RENEWABLE_SUBSCRIPTION) {
            return $this->onRecurringSuccess($payload);
        }

        return $this->onOneTimeSuccess($payload);
    }

    private function onOneTimeSuccess(JWSTransactionDecodedPayload $payload): bool
    {
        $order = $this->getOrderRepository()->getByGatewayOrderId($payload->getOriginalTransactionId(), $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $transaction = [
            'id'       => $payload->getTransactionId(),
            'currency' => $payload->getCurrency(),
            'amount'   => (int) ($payload->getPrice()) / 1000,
            'status'   => 'completed',
            'raw_data' => $this->retrieveRawTransaction($payload),
        ];

        Payment::onPaymentSuccess($order, $transaction, $this->retrieveRawTransaction($payload));

        return true;
    }

    private function onRecurringSuccess(JWSTransactionDecodedPayload $payload): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($payload->getOriginalTransactionId(), $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $transaction = [
            'id'       => $payload->getTransactionId(),
            'currency' => $payload->getCurrency(),
            'amount'   => (int) ($payload->getPrice()) / 1000,
            'status'   => 'completed',
            'raw_data' => $this->retrieveRawTransaction($payload),
        ];

        Payment::onPaymentSuccess($order, $transaction, $this->retrieveRawTransaction($payload));

        Payment::onSubscriptionActivated($order, [
            'gateway_subscription_id' => Arr::get($transaction, 'gateway_order_id'),
            'amount'                  => Arr::get($transaction, 'amount'),
        ]);

        return true;
    }

    private function onExpired(JWSTransactionDecodedPayload $payload): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($payload->getOriginalTransactionId(), $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        $amount = (int) $payload->getPrice() / 1000;

        Payment::onSubscriptionExpired($order, [
            'gateway_subscription_id' => $payload->getOriginalTransactionId(),
            'amount'                  => $amount,
        ]);

        return true;
    }

    private function onCancelled(JWSTransactionDecodedPayload $payload): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($payload->getOriginalTransactionId(), $this->gateway?->id);
        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }

        $amount = (int) ($payload->getPrice()) / 1000;

        Payment::onSubscriptionCancelled($order, [
            'gateway_subscription_id' => $payload->getOriginalTransactionId(),
            'amount'                  => $amount,
        ]);

        return true;
    }

    private function onRenewal(JWSTransactionDecodedPayload $payload): bool
    {
        $order = $this->getOrderRepository()->getByGatewaySubscriptionId($payload->getOriginalTransactionId(), $this->gateway?->id);

        if (!$order instanceof Order) {
            throw new RuntimeException(__p('in-app-purchase::phrase.the_requested_order_cannot_be_found'));
        }
        $amount      = (int) ($payload->getPrice()) / 1000;
        $transaction = [
            'id'       => $payload->getTransactionId(),
            'currency' => $payload->getCurrency(),
            'amount'   => $amount,
            'status'   => 'completed',
            'raw_data' => $this->retrieveRawTransaction($payload),
        ];

        Payment::onSubscriptionRecycled($order, [
            'gateway_subscription_id' => $payload->getOriginalTransactionId(),
            'amount'                  => $amount,
        ], $transaction);

        return true;
    }

    private function retrieveRawTransaction(JWSTransactionDecodedPayload $payload): array
    {
        return [
            'original_transaction_id' => $payload->getOriginalTransactionId(),
            'transaction_id'          => $payload->getTransactionId(),
            'expires_date'            => $payload->getExpiresDate(),
            'bundle_id'               => $payload->getBundleId(),
            'app_account_token'       => $payload->getAppAccountToken(),
            'is_upgraded'             => $payload->getIsUpgraded(),
            'offer_identifier'        => $payload->getOfferIdentifier(),
            'product_id'              => $payload->getProductId(),
            'purchase_date'           => $payload->getPurchaseDate(),
            'quantity'                => $payload->getQuantity(),
            'price'                   => $payload->getPrice(),
            'currency'                => $payload->getCurrency(),
        ];
    }
}
