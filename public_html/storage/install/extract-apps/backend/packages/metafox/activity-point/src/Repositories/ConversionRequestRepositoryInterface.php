<?php

namespace MetaFox\ActivityPoint\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Support\PointConversion as Support;

/**
 * Interface ConversionRequest
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ConversionRequestRepositoryInterface
{
    /**
     * @param User   $user
     * @param int    $points
     * @param string $currency
     * @return mixed
     */
    public function createConversionRequest(User $user, int $points, string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY): ConversionRequest;

    /**
     * @param User        $user
     * @param ConversionRequest $request
     * @return ConversionRequest
     */
    public function cancelConversionRequest(User $user, ConversionRequest $request): ConversionRequest;

    /**
     * @param User        $user
     * @param ConversionRequest $request
     * @return ConversionRequest
     */
    public function approveConversionRequest(User $user, ConversionRequest $request): ConversionRequest;

    /**
     * @param User        $user
     * @param ConversionRequest $request
     * @param string|null $reason
     * @return ConversionRequest
     */
    public function denyConversionRequest(User $user, ConversionRequest $request, ?string $reason = null): ConversionRequest;

    /**
     * @param User  $user
     * @param array $attributes
     * @return Paginator
     */
    public function viewConversionRequests(User $user, array $attributes = []): Paginator;

    /**
     * @param array $attributes
     * @return Paginator
     */
    public function viewConversionRequestAdminCP(array $attributes = []): Paginator;
}
