<?php
namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\Form;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\UserBalance;

class ReduceUserBalanceForm extends AbstractAdjustBalanceForm
{
    protected function prepare(): void
    {
        $this->title(__p('ewallet::admin.reduce_amount_from_balance'))
            ->asPost()
            ->action('admincp/emoney/user-balance/reduce')
            ->setValue([
                'user_id' => $this->resource->entityId(),
            ]);
    }

    public function getMinAndMaxCurrencyAmountValues(): array
    {
        $currentValues = $this->getCurrentUserBalances();

        $values = [];

        foreach ($this->currencies as $key => $currency) {
            $currencyCode = Arr::get($currency, 'value');

            $currentBalance = (float) Arr::get($currentValues, $currencyCode, 0);

            $min = UserBalance::getMinValueForReducing($currentBalance);

            $max = UserBalance::getMaxValueForReducing($currentBalance);

            if (0 == $min || 0 == $max) {
                unset($this->currencies[$key]);
                continue;
            }

            Arr::set($values, $currencyCode, [
                'min' => $min,
                'max' => $max,
            ]);
        }

        $this->currencies = array_values($this->currencies);

        return $values;
    }
}
