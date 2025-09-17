<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Models\Placement;
use MetaFox\Advertise\Notifications\AdminPaymentSuccessNotification;
use MetaFox\Advertise\Notifications\AdvertiseApprovedNotification;
use MetaFox\Advertise\Notifications\AdvertiseDeniedNotification;
use MetaFox\Advertise\Notifications\MarkAsPaidNotification;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Repositories\AdvertiseHideRepositoryInterface;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Repositories\CountryRepositoryInterface;
use MetaFox\Advertise\Repositories\GenderRepositoryInterface;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Repositories\LanguageRepositoryInterface;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Repositories\ReportRepositoryInterface;
use MetaFox\Advertise\Repositories\StatisticRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\FilterConditionServiceInterface;
use MetaFox\Advertise\Support\Browse\Scopes\Advertise\StatusScope;
use MetaFox\Advertise\Support\Support;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AdvertiseRepository.
 */
class AdvertiseRepository extends AbstractRepository implements AdvertiseRepositoryInterface
{
    public const SHOW_CACHE_ID  = 'advertise_placement_show_';
    public const SHOW_CACHE_TTL = 3600;

    public function model()
    {
        return Advertise::class;
    }

    protected function invoiceRepository(): InvoiceRepositoryInterface
    {
        return resolve(InvoiceRepositoryInterface::class);
    }

    public function paymentAdvertise(User $context, int $id, int $gatewayId): array
    {
        /**
         * @var Advertise $advertise
         */
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'payment', $context, $advertise);

        $order = Payment::initOrder($advertise);

        if (null === $order) {
            return [];
        }

        $url = $advertise->toUrl();

        return Payment::placeOrder($order, $gatewayId, [
            'return_url'  => $url,
            'cancel_url'  => $url,
            'description' => $advertise->toPaymentDescription(),
        ]);
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Advertise
     * @throws AuthorizationException
     */
    public function createAdvertise(User $context, array $attributes): Advertise
    {
        $placement = resolve(PlacementRepositoryInterface::class)->find(Arr::get($attributes, 'placement_id'));

        if (!$placement->is_active) {
            throw new AuthorizationException(__p('advertise::phrase.placement_not_available'));
        }

        $price = 0;
        $total = match ($placement->placement_type) {
            Support::PLACEMENT_CPM => Arr::get($attributes, 'total_impression'),
            Support::PLACEMENT_PPC => Arr::get($attributes, 'total_click'),
            default                => null,
        };

        $currencyId = app('currency')->getUserCurrencyId($context);

        if (!is_numeric($total) || $total < 1) {
            throw new AuthorizationException(__p('advertise::phrase.invalid_ad'));
        }

        if (!$context->hasPermissionTo('advertise.free_payment')) {
            if (!Arr::has($placement->price, $currencyId)) {
                throw new AuthorizationException();
            }

            $price = $this->calculatePrice($total, $placement->placement_type, Arr::get($placement->price, $currencyId));

            if (null === $price) {
                throw new AuthorizationException(__p('advertise::phrase.invalid_ad'));
            }
        }

        $advertise = $this->insertData($context, $attributes);

        $this->invoiceRepository()->createInvoice($context, $advertise, [
            'price'           => $price,
            'payment_gateway' => 0,
            'currency_id'     => $currencyId,
            'delay_payment'   => true,
        ]);

        $advertise->refresh();

        $this->clearCaches($placement->entityId());

        return $advertise;
    }

    protected function calculatePrice(int $total, string $placementType, ?float $placementPrice): ?float
    {
        if (null === $placementPrice) {
            return null;
        }

        if ($placementPrice == 0) {
            return 0;
        }

        if ($placementType == Support::PLACEMENT_PPC) {
            return round($total * $placementPrice, 2);
        }

        /**
         * Cost per mille.
         */
        $price = ($placementPrice * $total) / 1000;

        return round($price, 2);
    }

    public function updateAdvertise(User $context, int $id, array $attributes): Advertise
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'update', $context, $advertise);

        $advertise->fill($attributes);

        $advertise->save();

        $this->addGenders($advertise, Arr::get($attributes, 'genders'));

        $this->addLanguages($advertise, Arr::get($attributes, 'languages'));

        $this->addLocation($advertise, Arr::get($attributes, 'location'));

        $advertise->refresh();

        $this->clearCaches($advertise->placement_id);

        return $advertise;
    }

    public function deleteAdvertise(User $context, int $id): bool
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'delete', $context, $advertise);

        return $this->deleteItem($advertise);
    }

    public function createAdvertiseAdminCP(User $context, array $attributes): Advertise
    {
        policy_authorize(AdvertisePolicy::class, 'createAdminCP', $context);

        Arr::set($attributes, 'status', Support::ADVERTISE_STATUS_APPROVED);

        $advertise = $this->insertData($context, $attributes);

        $this->invoiceRepository()->createInvoiceAdminCP($context, $advertise);

        return $advertise;
    }

    protected function insertData(User $context, array $attributes): Advertise
    {
        /**
         * Note: AdminCP will add status to $attributes.
         */
        $status = Arr::get($attributes, 'status');

        if (null === $status) {
            $status = Support::ADVERTISE_STATUS_UNPAID;
        }

        $placement = resolve(PlacementRepositoryInterface::class)->find(Arr::get($attributes, 'placement_id'));

        switch ($placement->placement_type) {
            case Support::PLACEMENT_CPM :
                Arr::forget($attributes, 'total_click');
                break;
            default:
                Arr::forget($attributes, 'total_impression');
        }

        $advertise =  $this->getModel()->newModelInstance(array_merge($attributes, [
            'user_id'           => $context->entityId(),
            'user_type'         => $context->entityType(),
            'status'            => $status,
            'advertise_type'    => $placement->placement_type,
            'advertise_file_id' => Arr::get($attributes, 'image.temp_file'),
        ]));

        $advertise->save();

        $this->addGenders($advertise, Arr::get($attributes, 'genders'));

        $this->addLanguages($advertise, Arr::get($attributes, 'languages'));

        $this->addLocation($advertise, Arr::get($attributes, 'location'));

        /*
         * TODO: consider to implement filter by location (country_iso, child_id, state_id)
         */

        resolve(StatisticRepositoryInterface::class)->createStatistic($advertise);

        $advertise->refresh();

        $this->clearCaches($advertise->placement_id);

        return $advertise;
    }

    protected function addLocation(Advertise $advertise, ?array $locations): void
    {
        resolve(CountryRepositoryInterface::class)->createLocation($advertise, $locations);
    }

    protected function addGenders(Advertise $advertise, ?array $genders): void
    {
        resolve(GenderRepositoryInterface::class)->addGenders($advertise, $genders);
    }

    protected function addLanguages(Advertise $advertise, ?array $languages): void
    {
        resolve(LanguageRepositoryInterface::class)->addLanguages($advertise, $languages);
    }

    public function updateAdvertiseAdminCP(User $context, int $id, array $attributes): Advertise
    {
        policy_authorize(AdvertisePolicy::class, 'updateAdminCP', $context);

        $advertise = $this
            ->with(['placement'])
            ->find($id);

        $oldPlacement = $advertise->placement;

        $placement = resolve(PlacementRepositoryInterface::class)->find(Arr::get($attributes, 'placement_id'));

        $attributes = array_merge($attributes, [
            'advertise_type' => $placement->placement_type,
        ]);

        $tempFileId = Arr::get($attributes, 'image.temp_file');

        if (is_numeric($tempFileId)) {
            Arr::set($attributes, 'advertise_file_id', $tempFileId);
            upload()->rollUp($advertise->advertise_file_id);
        }

        $advertise->fill($attributes);

        $advertise->save();

        $this->addGenders($advertise, Arr::get($attributes, 'genders'));

        $this->addLanguages($advertise, Arr::get($attributes, 'languages'));

        $this->addLocation($advertise, Arr::get($attributes, 'location'));

        $advertise->refresh();

        $this->handleChangePlacement($context, $advertise, $placement, $oldPlacement);

        $this->clearCaches($advertise->placement_id);

        return $advertise;
    }

    protected function handleChangePlacement(User $context, Advertise $advertise, Placement $newPlacement, ?Placement $oldPlacement): void
    {
        if ($advertise->status != Support::ADVERTISE_STATUS_UNPAID) {
            return;
        }

        $hasChanged = false;

        if (null === $oldPlacement) {
            $hasChanged = true;
        }

        if ($oldPlacement instanceof Placement) {
            $hasChanged = $oldPlacement->entityId() != $newPlacement->entityId();
        }

        if (!$hasChanged) {
            return;
        }

        if (null === $advertise->user) {
            return;
        }

        $currencyId = app('currency')->getUserCurrencyId($advertise->user);

        if (!$newPlacement->isFree($currencyId)) {
            return;
        }

        $this->invoiceRepository()->createInvoiceAdminCP($advertise->user, $advertise);

        if ($context->entityId() != $advertise->userId()) {
            $this->sendAdminPaymentSuccessNotification($context, $advertise->user, $advertise);
        }
    }

    protected function sendAdminPaymentSuccessNotification(User $context, ?User $notifiable, Advertise $advertise)
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new AdminPaymentSuccessNotification($advertise);

        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    protected function deleteItem(Advertise $advertise): bool
    {
        $advertise->delete();

        $this->clearCaches($advertise->placement_id);

        return true;
    }

    public function deleteAdvertiseAdminCP(User $context, int $id): bool
    {
        policy_authorize(AdvertisePolicy::class, 'deleteAdminCP', $context);

        $advertise = $this->find($id);

        return $this->deleteItem($advertise);
    }

    public function deleteData(Advertise $advertise)
    {
        upload()->rollUp($advertise->advertise_file_id);

        resolve(GenderRepositoryInterface::class)->deleteGenders($advertise);

        resolve(LanguageRepositoryInterface::class)->deleteLanguages($advertise);

        resolve(StatisticRepositoryInterface::class)->deleteStatistic($advertise);

        resolve(AdvertiseHideRepositoryInterface::class)->deleteHidesByItem($advertise);

        resolve(CountryRepositoryInterface::class)->deleteLocations($advertise);

        $advertise->unpaidInvoices()->delete();

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$advertise]);

        $advertise->invoices()->update(['item_deleted_title' => $advertise->toTitle()]);

        $this->clearCaches($advertise->placement_id);
    }

    public function viewAdvertiesForAdminCP(User $context, array $attributes = []): Paginator
    {
        policy_authorize(AdvertisePolicy::class, 'viewAdminCP', $context);

        $placementId  = Arr::get($attributes, 'placement_id');
        $startDate    = Arr::get($attributes, 'start_date');
        $endDate      = Arr::get($attributes, 'end_date');
        $title        = Arr::get($attributes, 'title');
        $userFullName = Arr::get($attributes, 'full_name');
        $status       = Arr::get($attributes, 'status');
        $active       = Arr::get($attributes, 'is_active');
        $limit        = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = Advertise::query()
            ->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'advertises.user_id');
            });

        if (is_numeric($placementId)) {
            $query->where('advertises.placement_id', '=', $placementId);
        }

        if ($startDate) {
            $query->where('advertises.start_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('advertises.end_date', '<=', $endDate);
        }

        if (is_string($title) && MetaFoxConstant::EMPTY_STRING != $title) {
            $title = trim($title);
            if ($title) {
                $query->where('advertises.title', $this->likeOperator(), '%' . $title . '%');
            }
        }

        if (is_string($userFullName) && MetaFoxConstant::EMPTY_STRING != $userFullName) {
            $userFullName = trim($userFullName);
            if ($userFullName) {
                $query->where('user_entities.name', $this->likeOperator(), '%' . $userFullName . '%');
            }
        }

        if (null !== $active) {
            $query->where('advertises.is_active', '=', (int) $active);
        }

        if (null !== $status) {
            $query->addScope(new StatusScope($status));
        }

        return $query
            ->orderByDesc('advertises.id')
            ->paginate($limit, ['advertises.*']);
    }

    public function activeAdvertise(User $context, int $id, bool $active): bool
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'active', $context, $advertise);

        $advertise->update(['is_active' => $active]);

        $this->clearCaches($advertise->placement_id);

        return true;
    }

    public function viewAdvertise(User $context, int $id): ?Advertise
    {
        $advertise = $this
            ->with(['placement', 'genders', 'languages'])
            ->find($id);

        policy_authorize(AdvertisePolicy::class, 'viewDetail', $context, $advertise);

        return $advertise;
    }

    public function updateSuccessPayment(Advertise $advertise, Invoice $invoice): bool
    {
        if ($advertise->status != Support::ADVERTISE_STATUS_UNPAID) {
            return false;
        }

        if ($invoice->itemId() != $advertise->entityId()) {
            return false;
        }

        if ($invoice->itemType() != $advertise->entityType()) {
            return false;
        }

        $status = Support::ADVERTISE_STATUS_PENDING;

        if ($advertise->user instanceof User && $advertise->user->hasPermissionTo('advertise.auto_approve')) {
            $status = Support::ADVERTISE_STATUS_APPROVED;
        }

        $advertise->update([
            'status' => $status,
        ]);

        if ($status == Support::ADVERTISE_STATUS_PENDING) {
            $advertise->toPendingItem($invoice);

            return true;
        }

        if ($advertise->is_active) {
            $this->clearCaches($advertise->placement_id);
        }

        return true;
    }

    public function viewAdvertises(User $context, array $attributes = []): Paginator
    {
        policy_authorize(AdvertisePolicy::class, 'viewAny', $context);

        $placementId = Arr::get($attributes, 'placement_id');
        $startDate   = Arr::get($attributes, 'start_date');
        $endDate     = Arr::get($attributes, 'end_date');
        $status      = Arr::get($attributes, 'status');

        $query = Advertise::query()
            ->where('advertises.user_id', '=', $context->entityId());

        if (is_numeric($placementId)) {
            $query->where('advertises.placement_id', '=', $placementId);
        }

        if ($startDate) {
            $query->whereNotNull('advertises.start_date')
                ->where('advertises.start_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereNotNull('advertises.start_date')
                ->where('advertises.start_date', '<=', $endDate);
        }

        if ($status) {
            $query->addScope(new StatusScope($status));
        }

        return $query
            ->orderByDesc('advertises.id')
            ->paginate(Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE), ['advertises.*']);
    }

    protected function filterAdvertiseByUserHidden(User $context, Collection $advertises): Collection
    {
        $hiddenIds = resolve(AdvertiseHideRepositoryInterface::class)->getHiddenItemIds($context, Advertise::ENTITY_TYPE);

        if (!count($hiddenIds)) {
            return $advertises;
        }

        return $advertises->filter(function ($advertise) use ($hiddenIds) {
            return !in_array($advertise->entityId(), $hiddenIds);
        });
    }

    public function showAdvertises(User $context, int $placementId, string $location): Collection
    {
        $placement = Placement::query()
            ->where('id', '=', $placementId)
            ->first();

        $emptyCollection = collect([]);

        if (null === $placement) {
            return $emptyCollection;
        }

        if (!policy_check(AdvertisePolicy::class, 'show', $context, $placement)) {
            return $emptyCollection;
        }

        $limit = 1;

        if (in_array($location, [Support::LOCATION_SUB_SIDE, Support::LOCATION_SIDE])) {
            $limit = Settings::get('advertise.maximum_number_of_advertises_on_side_block', 3);
        }

        if (0 == $limit) {
            return $emptyCollection;
        }

        $advertises = Cache::remember(self::SHOW_CACHE_ID . $placementId, self::SHOW_CACHE_TTL, function () use ($placementId) {
            return Advertise::query()
                ->where([
                    'placement_id' => $placementId,
                    'status'       => Support::ADVERTISE_STATUS_APPROVED,
                    'is_active'    => true,
                ])
                ->where('start_date', '<=', Carbon::now())
                ->where(function ($builder) {
                    $builder->whereNull('end_date')
                        ->orWhere('end_date', '>', Carbon::now());
                })
                ->get()
                ->filter(function ($advertise) {
                    return $this->filterActiveAdvertise($advertise);
                })
                ->values();
        });

        if (!$advertises->count()) {
            return $advertises;
        }

        $advertises = $advertises->shuffle();

        $hiddenIds = resolve(AdvertiseHideRepositoryInterface::class)->getHiddenItemIds($context, Advertise::ENTITY_TYPE);

        $advertiseIds = [];

        /**
         * @var FilterConditionServiceInterface $filterService
         */
        $filterService = resolve(FilterConditionServiceInterface::class);

        foreach ($advertises as $advertise) {
            if (!$filterService->filterByUserInformation($context, $advertise)) {
                continue;
            }

            if (!$filterService->filterByUserLocation($context, $advertise)) {
                continue;
            }

            if (in_array($advertise->entityId(), $hiddenIds)) {
                continue;
            }

            if (!$filterService->filterByDate($advertise)) {
                continue;
            }

            $advertiseIds[] = $advertise->entityId();

            if (count($advertiseIds) >= $limit) {
                break;
            }
        }

        if (!count($advertiseIds)) {
            return collect([]);
        }

        return Advertise::query()
            ->whereIn('id', $advertiseIds)
            ->get()
            ->shuffle()
            ->values();
    }

    public function updateTotal(User $context, int $id, string $type): ?Advertise
    {
        $advertise = $this->find($id);

        if (!policy_check(AdvertisePolicy::class, 'updateTotal', $context, $advertise)) {
            return null;
        }

        match ($type) {
            Support::TYPE_IMPRESSION => $advertise->statistic->incrementAmount('total_impression'),
            Support::TYPE_CLICK      => $advertise->statistic->incrementAmount('total_click'),
            default                  => null,
        };

        $advertise->load(['statistic']);

        $isCompleted = match ($advertise->advertise_type) {
            Support::PLACEMENT_PPC => $advertise->total_click > 0 && $advertise->total_click <= $advertise->statistic->total_click,
            Support::PLACEMENT_CPM => $advertise->total_impression > 0 && $advertise->total_impression <= $advertise->statistic->total_impression,
        };

        if ($isCompleted) {
            $advertise->update(['status' => Support::ADVERTISE_STATUS_COMPLETED, 'completed_at' => Carbon::now()]);
            $this->clearCaches($advertise->placement_id);
        }

        $totalType = match ($type) {
            Support::TYPE_CLICK      => 'total_click',
            Support::TYPE_IMPRESSION => 'total_impression',
            default                  => null,
        };

        if (null != $totalType) {
            resolve(ReportRepositoryInterface::class)->createReport($advertise, $totalType);
        }

        return $advertise->refresh();
    }

    protected function filterActiveAdvertise(Advertise $advertise): bool
    {
        $total = match ($advertise->advertise_type) {
            Support::PLACEMENT_CPM => $advertise->total_impression,
            Support::PLACEMENT_PPC => $advertise->total_click,
            default                => null,
        };

        if (null === $total) {
            return false;
        }

        if (0 == $total) {
            return true;
        }

        if (null === $advertise->statistic) {
            return false;
        }

        $current = match ($advertise->advertise_type) {
            Support::PLACEMENT_CPM => $advertise->statistic->total_impression,
            Support::PLACEMENT_PPC => $advertise->statistic->total_click,
        };

        return $total > $current;
    }

    public function clearCaches(int $placementId): void
    {
        Cache::delete(self::SHOW_CACHE_ID . $placementId);
    }

    public function viewReport(User $context, int $id, string $view, string $totalType, array $dates = []): array
    {
        $advertise = resolve(AdvertiseRepositoryInterface::class)->find($id);

        policy_authorize(AdvertisePolicy::class, 'viewReport', $context, $advertise);

        return resolve(ReportRepositoryInterface::class)->viewReport($advertise, $view, $totalType, Arr::get($dates, 'start'), Arr::get($dates, 'end'));
    }

    public function hideAdvertise(User $context, int $id): bool
    {
        $advertise = $this->find($id);

        resolve(AdvertiseHideRepositoryInterface::class)->createHide($context, $advertise);

        $this->clearCaches($advertise->placement_id);

        return true;
    }

    public function approveAdvertise(User $context, int $id): bool
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'approve', $context, $advertise);

        $advertise->is_approved = true;

        $advertise->save();

        if ($advertise->userId() != $context->entityId()) {
            $this->sendApprovedNotification($context, $advertise->user, $advertise);
        }

        return true;
    }

    protected function sendApprovedNotification(User $context, ?User $notifiable, Advertise $advertise): void
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new AdvertiseApprovedNotification($advertise);
        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    public function denyAdvertise(User $context, int $id): bool
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'deny', $context, $advertise);

        $advertise->is_denied = true;

        $advertise->save();

        if ($advertise->userId() != $context->entityId()) {
            $this->sendDeniedNotification($context, $advertise->user, $advertise);
        }

        return true;
    }

    protected function sendDeniedNotification(User $context, ?User $notifiable, Advertise $advertise): void
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new AdvertiseDeniedNotification($advertise);
        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    public function markAsPaid(User $context, int $id): bool
    {
        $advertise = $this->find($id);

        policy_authorize(AdvertisePolicy::class, 'markAsPaid', $context, $advertise);

        $user = $advertise->user;

        if (!$user instanceof User) {
            $user = $context;
        }

        $this->invoiceRepository()->createInvoiceAdminCP($user, $advertise);

        if ($user->entityId() != $context->entityId()) {
            $this->sendMarkAsPaidNotification($context, $user, $advertise);
        }

        return true;
    }

    protected function sendMarkAsPaidNotification(User $context, ?User $notifiable, Advertise $advertise)
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new MarkAsPaidNotification($advertise);
        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }
}
