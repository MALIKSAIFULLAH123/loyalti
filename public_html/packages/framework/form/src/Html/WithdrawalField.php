<?php
namespace MetaFox\Form\Html;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Traits\HasBoundValueTrait;
use MetaFox\Form\Traits\HasRelatedFieldNameTrait;

class WithdrawalField extends AbstractField
{
    use HasRelatedFieldNameTrait;
    use HasBoundValueTrait;

    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_WITHDRAWAL);
    }

    public function amountCalculation(array $configs): static
    {
        return $this->setAttribute('amountCalculation', $configs);
    }

    public function relatedFieldConfigs(array $relatedConfigs): static
    {
        $configs = collect($relatedConfigs)
            ->map(function ($relatedConfig) {
                return [
                    'required' => Arr::get($relatedConfig, 'required'),
                    'currency' => Arr::get($relatedConfig, 'currency'),
                    'min' => Arr::get($relatedConfig, 'min'),
                    'max' => Arr::get($relatedConfig, 'max'),
                    'description' => Arr::get($relatedConfig, 'description'),
                    'balanceDescription' => Arr::get($relatedConfig, 'balance_description'),
                    'amountCalculation' => Arr::get($relatedConfig, 'amount_calculation'),
                ];
            })
            ->keyBy('currency')
            ->toArray();

        return $this->setAttribute('relatedFieldConfigs', $configs);
    }

    public function balanceDescription(?string $description = null): static
    {
        return $this->setAttribute('balanceDescription', $description);
    }
}
