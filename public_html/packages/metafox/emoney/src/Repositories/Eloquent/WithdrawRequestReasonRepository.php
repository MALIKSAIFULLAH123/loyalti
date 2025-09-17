<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\EMoney\Repositories\WithdrawRequestReasonRepositoryInterface;
use MetaFox\EMoney\Models\WithdrawRequestReason;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class WithdrawRequestReasonRepository.
 */
class WithdrawRequestReasonRepository extends AbstractRepository implements WithdrawRequestReasonRepositoryInterface
{
    public function model()
    {
        return WithdrawRequestReason::class;
    }

    public function createReason(WithdrawRequest $request, string $reason, string $type = Support::WITHDRAW_REQUEST_REASON_TYPE_CANCEL): WithdrawRequestReason
    {
        return $this->getModel()->newQuery()->updateOrCreate([
            'request_id' => $request->entityId(),
            'type'       => $type,
        ], [
            'message' => $reason,
        ]);
    }
}
