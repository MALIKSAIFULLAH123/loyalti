<?php
namespace MetaFox\ActivityPoint\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Platform\Facades\Settings;
use MetaFox\ActivityPoint\Support\PointConversion as Support;

class AmountReceivedField extends AbstractField
{
    public const COMPONENT = 'PointConversionAmountReceived';

    private string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY;

    public function initialize(): void
    {
        $rate = (float) Settings::get(sprintf('activitypoint.conversion_rate.%s', $this->currency), 0);

        $feePercentage = (float) Settings::get('activitypoint.conversion_request_fee', 0);

        $feePercentage = $feePercentage / 100;

        $this->component(self::COMPONENT)
            ->name('amount_received_description')
            ->label(__p('activitypoint::phrase.amount_received'))
            ->exchangeRate($rate)
            ->exchangeRatePattern($this->currency)
            ->relatedField('points')
            ->feePercentage($feePercentage);
    }

    public function feePercentage(float $value): self
    {
        return $this->setAttribute('feePercentage', $value);
    }

    public function relatedField(string $name): self
    {
        return $this->setAttribute('relatedField', $name);
    }

    public function exchangeRate(float $rate): self
    {
        return $this->setAttribute('exchangeRate', $rate);
    }

    public function exchangeRatePattern(string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY): self
    {
        return $this->setAttribute('exchangeRatePattern', app('currency')->getFormatForPrice($currency, null, true));
    }
}
