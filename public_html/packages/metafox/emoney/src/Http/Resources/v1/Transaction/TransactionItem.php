<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\Transaction as Model;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItem;
use MetaFox\User\Models\UserEntity;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class TransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class TransactionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $result = [
            'id'            => $this->resource->entityId(),
            'module_name'   => Emoney::getAppAlias(),
            'resource_name' => $this->getResourceName(),
            'gross'         => $this->getPriceFormat($this->resource->total_currency, $this->resource->total_price),
            'fee'           => $this->getPriceFormat($this->resource->commission_currency, $this->resource->commission_price),
            'net'           => $this->getPriceFormat($this->resource->actual_currency, $this->resource->actual_price),
            'balance'       => $this->getBalance(),
            'status'        => $this->getStatus(),
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
            'source'        => $this->resource->source_text,
            'type'          => $this->resource->type_text,
            'reference'     => $this->getSource(),
        ];

        $result = $this->addCurrentBalanceAttribute($result);

        return $this->addBuyerAttribute($result);
    }

    protected function getResourceName(): string
    {
        if (Emoney::isUsingNewAlias()) {
            return $this->resource->entityType();
        }

        return 'emoney_transaction';
    }

    protected function getBalance(): array|string|null
    {
        $balance = $this->resource->balanceInfo();

        if ($this->isOldMobileVersion()) {
            return Arr::get($balance, 'value');
        }

        return $balance;
    }

    protected function getStatus(): array|string|null
    {
        $status = $this->resource->statusInfo();

        if ($this->isOldMobileVersion()) {
            return Arr::get($status, 'label');
        }

        return $status;
    }

    /**
     * @deprecated  Remove in 5.1.15
     */
    protected function addCurrentBalanceAttribute(array $result): array
    {
        if (!$this->isOldMobileVersion()) {
            return $result;
        }

        $currentBalance = null;

        if (is_numeric($this->resource->current_balance_price)) {
            $currentBalance = $this->getPriceFormat($this->resource->balance_currency, $this->resource->current_balance_price);
        }

        $result['current_balance'] = $currentBalance;

        return $result;
    }

    protected function addBuyerAttribute(array $result): array
    {
        $buyer = ['full_name' => __p('core::phrase.deleted_user')];

        if ($this->resource->userEntity instanceof UserEntity) {
            $buyer = new UserEntityItem($this->resource->userEntity);
        }

        if ($this->isOldMobileVersion()) {
            $result['buyer'] = $buyer instanceof UserEntityItem ? $buyer : null;

            return $result;
        }

        if ($this->isWithdrawnDeniedByAdmin()) {
            $buyer = ['full_name' => __p('ewallet::phrase.an_admin')];
        }

        $result['buyer'] = $buyer;

        return $result;
    }

    protected function isWithdrawnDeniedByAdmin(): bool
    {
        if ($this->resource->source != Support::TRANSACTION_SOURCE_INCOMING) {
            return false;
        }

        if ($this->resource->type != Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN) {
            return false;
        }

        if ($this->resource->userId() != $this->resource->ownerId()) {
            return false;
        }

        return $this->resource->isSystemActor();
    }

    protected function getSource(): string
    {
        $source = $this->resource->package?->title;

        if (null === $source) {
            $source = __p('ewallet::phrase.unknown');
        }

        return $source;
    }

    protected function isOldMobileVersion(): bool
    {
        if (!MetaFox::isMobile()) {
            return false;
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.13', '>=')) {
            return false;
        }

        return true;
    }

    protected function getPriceFormat(string $currency, float $price): ?string
    {
        return app('currency')->getPriceFormatByCurrencyId($currency, $price);
    }
}
