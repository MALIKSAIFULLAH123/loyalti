<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

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
        $userResource = null;

        if (null !== $this->resource->userEntity) {
            $userResource = new UserEntityDetail($this->resource->userEntity);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => Emoney::getAppAlias(),
            'resource_name'     => $this->resource->entityType(),
            'status'            => $this->resource->status_text,
            'total'             => $this->resource->total_text,
            'fee'               => $this->resource->fee_text,
            'amount'            => $this->resource->amount_text,
            'withdraw_method'   => $this->resource->withdrawMethod?->title,
            'user'              => $userResource,
            'reason'            => $this->resource->reason?->message,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'extra'             => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(WithdrawRequest::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_approve'     => $policy->approve($context, $this->resource),
            'can_deny'        => $policy->deny($context, $this->resource),
            'can_view_reason' => $policy->viewReason($context, $this->resource),
            'can_payment'     => $policy->payment($context, $this->resource),
        ];
    }
}
