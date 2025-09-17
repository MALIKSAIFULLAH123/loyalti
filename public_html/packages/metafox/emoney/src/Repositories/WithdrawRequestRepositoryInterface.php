<?php

namespace MetaFox\EMoney\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface WithdrawRequest.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface WithdrawRequestRepositoryInterface
{
    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewRequests(User $user, array $attributes = []): Paginator;

    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function manageRequests(User $user, array $attributes = []): Paginator;

    /**
     * @param  User            $user
     * @param  string          $currency
     * @param  float           $amount
     * @param  string          $withdrawService
     * @return WithdrawRequest
     */
    public function createRequest(User $user, string $currency, float $amount, string $withdrawService): WithdrawRequest;

    /**
     * @param  User            $user
     * @param  WithdrawRequest $request
     * @param  string|null     $reason
     * @return mixed
     */
    public function cancelRequest(User $user, WithdrawRequest $request, ?string $reason = null);

    /**
     * @param  User            $user
     * @param  WithdrawRequest $request
     * @param  string|null     $reason
     * @return mixed
     */
    public function denyRequest(User $user, WithdrawRequest $request, ?string $reason = null);

    /**
     * @param  User            $user
     * @param  WithdrawRequest $request
     * @return array|null
     */
    public function approveRequest(User $user, WithdrawRequest $request): ?array;

    /**
     * @param  User            $user
     * @param  WithdrawRequest $request
     * @return array|null
     */
    public function paymentRequest(User $user, WithdrawRequest $request): ?array;

    /**
     * @param  Order       $order
     * @param  Transaction $transaction
     * @return bool
     */
    public function updateSuccessPayment(Order $order, Transaction $transaction): bool;
}
