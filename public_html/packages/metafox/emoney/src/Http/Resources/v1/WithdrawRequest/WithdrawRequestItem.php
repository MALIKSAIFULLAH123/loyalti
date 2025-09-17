<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\Platform\Facades\PolicyGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class WithdrawRequestItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class WithdrawRequestItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => Emoney::getAppAlias(),
            'resource_name'     => $this->getResourceName(),
            'status'            => $this->resource->status_text,
            'total'             => $this->resource->total_text,
            'fee'               => $this->resource->fee_text,
            'amount'            => $this->resource->amount_text,
            'withdraw_method'   => $this->resource->withdrawMethod?->title,
            'reason'            => $this->resource->reason?->message,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'extra'             => $this->getExtra(),
        ];
    }

    private function getResourceName(): string
    {
        if (Emoney::isUsingNewAlias()) {
            return $this->resource->entityType();
        }

        return 'emoney_withdraw_request';
    }

    protected function getExtra(): array
    {
        /**
         * @var WithdrawRequestPolicy $policy
         */
        $policy = PolicyGate::getPolicyFor(WithdrawRequest::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_cancel'      => $policy->cancel($context, $this->resource),
            'can_view_reason' => $policy->viewReason($context, $this->resource),
        ];
    }
}
