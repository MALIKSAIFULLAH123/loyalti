<?php

namespace MetaFox\Localize\Http\Resources\v1\Currency\Admin;

use Illuminate\Support\Arr;
use MetaFox\Localize\Repositories\CurrencyRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * EditCurrencyForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditCurrencyForm.
 * @driverType form
 * @driverName core.currency.update
 */
class UpdateCurrencyForm extends StoreCurrencyForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(CurrencyRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $data = $this->resource->toArray();

        Arr::set($data, 'currency_code', $data['code']);
        Arr::set($data, 'is_using', (int) $this->resource->is_using);

        $this->asPut()
            ->title(__p('localize::currency.edit_currency'))
            ->action(url_utility()->makeApiUrl('/admincp/localize/currency/' . $this->resource->entityId()))
            ->setValue($data);
    }

    protected function isDisable(): bool
    {
        return $this->resource->is_default;
    }
}
