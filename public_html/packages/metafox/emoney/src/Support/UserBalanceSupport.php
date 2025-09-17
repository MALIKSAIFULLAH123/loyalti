<?php
namespace MetaFox\EMoney\Support;

use MetaFox\EMoney\Contracts\UserBalanceSupportInterface;

class UserBalanceSupport implements UserBalanceSupportInterface
{

    public function getMinValueForSending(float $currentBalance): float
    {
        $restAvailable = $this->getRestAvailableAmountForAdjustment($currentBalance);

        return min($restAvailable, Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE);
    }

    public function getMaxValueForSending(float $currentBalance): float
    {
        $restAvailable = $this->getRestAvailableAmountForAdjustment($currentBalance);

        if ($restAvailable <= 0) {
            return 0;
        }

        if ($restAvailable >= Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL) {
            return Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL;
        }

        if ($restAvailable > Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE) {
            return $restAvailable;
        }

        return max(Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE, $restAvailable);
    }

    public function getMinValueForReducing(float $currentBalance): float
    {
        if ($currentBalance <= 0) {
            return 0;
        }

        return min($currentBalance, Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE);
    }

    public function getMaxValueForReducing(float $currentBalance): float
    {
        if ($currentBalance <= 0) {
            return 0;
        }

        return min($currentBalance, Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL);
    }

    public function getRestAvailableAmountForAdjustment(float $currentBalance): float
    {
        return max(Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL - $currentBalance, 0);
    }

    public function getAdjustmentTypeOptions(): array
    {
        return [
            [
                'label' => __p('ewallet::phrase.sent'),
                'value' => Support::USER_BALANCE_ACTION_SEND,
            ],
            [
                'label' => __p('ewallet::phrase.reduced'),
                'value' => Support::USER_BALANCE_ACTION_REDUCE,
            ],
        ];
    }
}
