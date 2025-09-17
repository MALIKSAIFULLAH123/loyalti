<?php
namespace MetaFox\EMoney\Contracts;

interface UserBalanceSupportInterface
{
    /**
     * @param float $currentBalance
     * @return float
     */
    public function getMinValueForSending(float $currentBalance): float;

    /**
     * @param float $currentBalance
     * @return float
     */
    public function getMaxValueForSending(float $currentBalance): float;

    /**
     * @param float $currentBalance
     * @return float
     */
    public function getMinValueForReducing(float $currentBalance): float;

    /**
     * @param float $currentBalance
     * @return float
     */
    public function getMaxValueForReducing(float $currentBalance): float;

    /**
     * @param float $currentBalance
     * @return float
     */
    public function getRestAvailableAmountForAdjustment(float $currentBalance): float;

    /**
     * @return array
     */
    public function getAdjustmentTypeOptions(): array;
}
