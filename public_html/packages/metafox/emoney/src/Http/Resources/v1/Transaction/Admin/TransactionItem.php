<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction\Admin;

use Illuminate\Support\Carbon;
use MetaFox\EMoney\Models\Transaction as Model;
use MetaFox\EMoney\Support\Support;
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
class TransactionItem extends \MetaFox\EMoney\Http\Resources\v1\Transaction\TransactionItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'ewallet',
            'resource_name' => $this->resource->entityType(),
            'gross'         => $this->getPriceFormat($this->resource->total_currency, $this->resource->total_price),
            'fee'           => $this->getPriceFormat($this->resource->commission_currency, $this->resource->commission_price),
            'net'           => $this->getPriceFormat($this->resource->actual_currency, $this->resource->actual_price),
            'balance'       => $this->getPriceFormat($this->resource->balance_currency, $this->resource->balance_price),
            'status'        => $this->resource->status_text,
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
            'source'        => $this->resource->source_text,
            'type'          => $this->resource->type_text,
            'reference'     => $this->getSource(),
        ];

        return array_merge($data, $this->handleSenderAndReceiver());
    }

    protected function handleSenderAndReceiver(): array
    {
        $sender = $receiver = [
            'display_name' => __p('core::phrase.deleted_user'),
        ];

        switch ($this->resource->source) {
            case Support::TRANSACTION_SOURCE_OUTGOING:
                if ($this->resource->userEntity instanceof UserEntity) {
                    $sender = new UserEntityItem($this->resource->userEntity);
                }

                if ($this->resource->userId() == $this->resource->ownerId()) {
                    $receiver = ['display_name' => __p('core::phrase.system')];
                    break;
                }

                if ($this->resource->ownerEntity instanceof UserEntity) {
                    $receiver = new UserEntityItem($this->resource->ownerEntity);
                }

                break;
            case Support::TRANSACTION_SOURCE_INCOMING:
                if ($this->resource->ownerEntity instanceof UserEntity) {
                    $receiver = new UserEntityItem($this->resource->ownerEntity);
                }

                if ($this->resource->userId() == $this->resource->ownerId()) {
                    $sender = ['display_name' => __p('core::phrase.system')];
                    break;
                }

                if ($this->resource->userEntity instanceof UserEntity) {
                    $sender = new UserEntityItem($this->resource->userEntity);
                }
                break;
        }

        return [
            'sender'   => $sender,
            'receiver' => $receiver,
        ];
    }
}
