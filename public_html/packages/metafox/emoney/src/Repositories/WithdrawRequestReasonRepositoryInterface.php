<?php

namespace MetaFox\EMoney\Repositories;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Models\WithdrawRequestReason;
use MetaFox\EMoney\Support\Support;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface WithdrawRequestReason.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface WithdrawRequestReasonRepositoryInterface
{
    /**
     * @param  WithdrawRequest       $request
     * @param  string                $reason
     * @param  string                $type
     * @return WithdrawRequestReason
     */
    public function createReason(WithdrawRequest $request, string $reason, string $type = Support::WITHDRAW_REQUEST_REASON_TYPE_CANCEL): WithdrawRequestReason;
}
