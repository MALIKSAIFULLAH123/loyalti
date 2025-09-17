<?php

namespace MetaFox\EMoney\Support;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Contracts\PaymentInterface;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Facades\Payment as EwalletPayment;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\Payment\Models\Order;

class PaymentSupport implements PaymentInterface
{

    protected StatisticRepositoryInterface   $statisticRepository;
    protected TransactionRepositoryInterface $transactionRepository;
    protected ConversionRateServiceInterface $conversionRateService;

    public function __construct(
        StatisticRepositoryInterface   $statisticRepository,
        TransactionRepositoryInterface $transactionRepository,
        ConversionRateServiceInterface $conversionRateService
    ) {
        $this->statisticRepository   = $statisticRepository;
        $this->transactionRepository = $transactionRepository;
        $this->conversionRateService = $conversionRateService;
    }

    public function processPayment(Order $order, array $extra = []): ?array
    {
        if (null === $order->user) {
            return null;
        }

        $currencyPayment = Emoney::getPaymentBalanceCurrency($order, $extra);

        $amount = $this->convertAmount($order->total, $order->currency, $currencyPayment);

        if (null === $amount) {
            return null;
        }

        $balance = $this->statisticRepository->getUserBalance($order->user, $currencyPayment);

        if ($balance < $amount) {
            return null;
        }

        $gatewayOrderId = EwalletPayment::generateOrderId($order);
        Arr::set($extra, 'price_payment', $amount);

        $this->transactionRepository->createOutgoingTransaction($order, $gatewayOrderId, $extra);

        Arr::set($extra,'gateway_order_id',$gatewayOrderId);

        return $extra;
    }

    public function generateTransactionId(Order $order): string
    {
        return sprintf('et_%s', md5(uniqid() . $order->entityId() . $order->entityType()));
    }

    public function generateOrderId(Order $order): string
    {
        return sprintf('eo_%s', md5(uniqid() . $order->entityId() . $order->entityType()));
    }

    private function convertAmount(float $amount, string $baseCurrency, string $targetCurrency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): ?float
    {
        if ($baseCurrency == $targetCurrency) {
            return $amount;
        }

        return $this->conversionRateService->getConversedAmount($baseCurrency, $amount, $targetCurrency);
    }
}
