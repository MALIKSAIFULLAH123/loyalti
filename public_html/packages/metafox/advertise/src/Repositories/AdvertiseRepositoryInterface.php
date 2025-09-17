<?php

namespace MetaFox\Advertise\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Invoice;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface Advertise.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface AdvertiseRepositoryInterface
{
    /**
     * @param  User  $context
     * @param  int   $id
     * @param  int   $gatewayId
     * @return array
     */
    public function paymentAdvertise(User $context, int $id, int $gatewayId): array;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Advertise
     */
    public function createAdvertise(User $context, array $attributes): Advertise;

    /**
     * @param  User      $context
     * @param  int       $id
     * @param  array     $attributes
     * @return Advertise
     */
    public function updateAdvertise(User $context, int $id, array $attributes): Advertise;

    /**
     * @param  User      $context
     * @param  int       $id
     * @return Advertise
     */
    public function deleteAdvertise(User $context, int $id): bool;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Advertise
     */
    public function createAdvertiseAdminCP(User $context, array $attributes): Advertise;

    /**
     * @param  User      $context
     * @param  int       $id
     * @param  array     $attributes
     * @return Advertise
     */
    public function updateAdvertiseAdminCP(User $context, int $id, array $attributes): Advertise;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteAdvertiseAdminCP(User $context, int $id): bool;

    /**
     * @param  Advertise $advertise
     * @return mixed
     */
    public function deleteData(Advertise $advertise);

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAdvertiesForAdminCP(User $context, array $attributes = []): Paginator;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $active
     * @return bool
     */
    public function activeAdvertise(User $context, int $id, bool $active): bool;

    /**
     * @param  User           $context
     * @param  int            $id
     * @return Advertise|null
     */
    public function viewAdvertise(User $context, int $id): ?Advertise;

    /**
     * @param  Advertise $advertise
     * @param  Invoice   $invoice
     * @return bool
     */
    public function updateSuccessPayment(Advertise $advertise, Invoice $invoice): bool;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAdvertises(User $context, array $attributes = []): Paginator;

    /**
     * @param  User       $context
     * @param  int        $placementId
     * @param  string     $location
     * @return Collection
     */
    public function showAdvertises(User $context, int $placementId, string $location): Collection;

    /**
     * @param  User           $context
     * @param  int            $id
     * @param  string         $type
     * @return Advertise|null
     */
    public function updateTotal(User $context, int $id, string $type): ?Advertise;

    /**
     * @param  User   $context
     * @param  int    $id
     * @param  string $view
     * @param  string $totalType
     * @param  array  $dates
     * @return array
     */
    public function viewReport(User $context, int $id, string $view, string $totalType, array $dates = []): array;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function hideAdvertise(User $context, int $id): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function approveAdvertise(User $context, int $id): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function denyAdvertise(User $context, int $id): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function markAsPaid(User $context, int $id): bool;
}
