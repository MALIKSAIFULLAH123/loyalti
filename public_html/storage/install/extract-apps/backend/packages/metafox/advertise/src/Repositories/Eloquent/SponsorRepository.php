<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Notifications\MarkSponsorAsPaidNotification;
use MetaFox\Advertise\Notifications\SponsorApprovedNotification;
use MetaFox\Advertise\Notifications\SponsorDeniedNotification;
use MetaFox\Advertise\Repositories\CountryRepositoryInterface;
use MetaFox\Advertise\Repositories\GenderRepositoryInterface;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Repositories\LanguageRepositoryInterface;
use MetaFox\Advertise\Repositories\ReportRepositoryInterface;
use MetaFox\Advertise\Repositories\StatisticRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\FilterConditionServiceInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Browse\Scopes\Sponsor\StatusScope;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Platform\Contracts\User;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SponsorRepository.
 */
class SponsorRepository extends AbstractRepository implements SponsorRepositoryInterface
{
    public const PENDING_SPONSOR_CACHE_ID    = '%s_pending_sponsor_item_ids';
    public const APPROVED_SPONSOR_CACHE_ID   = '%s_approved_sponsor_item_ids';
    public const SPONSOR_CACHE_TIME          = 86400;

    public function model()
    {
        return Sponsor::class;
    }

    protected function resolveMorphedItemBuilder(string $itemType): ?array
    {
        $modelName = Relation::getMorphedModel($itemType);

        if (null === $modelName) {
            return null;
        }

        $model = resolve($modelName);

        if (!$model instanceof Content) {
            return null;
        }

        return [$model->newQuery(), $model->getKeyName()];
    }

    public function getMorphedItem(string $itemType, int $itemId): ?Content
    {
        /**
         * @var Builder $builder
         */
        $data = $this->resolveMorphedItemBuilder($itemType);

        if (null === $data) {
            return null;
        }

        $builder = array_shift($data);

        if (!$builder instanceof Builder) {
            return null;
        }

        return $builder->find($itemId);
    }

    public function getMorphedItems(string $itemType, array $itemIds): ?Collection
    {
        /**
         * @var Builder $builder
         */
        $data = $this->resolveMorphedItemBuilder($itemType);

        if (null === $data) {
            return null;
        }

        $builder = array_shift($data);

        $primaryKey = array_shift($data);

        if (!$builder instanceof Builder) {
            return null;
        }

        if (!$primaryKey) {
            return null;
        }

        return $builder
            ->whereIn($primaryKey, $itemIds)
            ->get();
    }

    public function getItemPolicy(string $itemType): mixed
    {
        $modelName = Relation::getMorphedModel($itemType);

        if (null === $modelName) {
            return null;
        }

        return PolicyGate::getPolicyFor($modelName);
    }

    public function sponsor(User $context, Content $item): Sponsor
    {
        $sponsor = $this->insertData($context, $item);

        resolve(InvoiceRepositoryInterface::class)->createInvoiceAdminCP($context, $sponsor);

        return $sponsor;
    }

    public function unsponsor(User $context, Content $item): bool
    {
        $item->disableSponsor();

        $this->clearCaches($item);

        $sponsor = $this->getModel()->newQuery()
            ->where([
                'item_type' => $item->entityType(),
                'item_id'   => $item->entityId(),
                'status'    => Support::ADVERTISE_STATUS_APPROVED,
            ])
            ->first();

        if (!$sponsor instanceof Sponsor) {
            return true;
        }

        $sponsor->delete();

        return true;
    }

    public function unsponsorFeed(User $context, Content $item): bool
    {
        if (null === $item->activity_feed) {
            return false;
        }

        $item->activity_feed->disableSponsor();

        $this->clearCaches($item->activity_feed);

        $sponsor = $this->getModel()->newQuery()
            ->where([
                'item_type' => $item->activity_feed->entityType(),
                'item_id'   => $item->activity_feed->entityId(),
                'status'    => Support::ADVERTISE_STATUS_APPROVED,
            ])
            ->first();

        if (!$sponsor instanceof Sponsor) {
            return true;
        }

        $sponsor->delete();

        return true;
    }

    public function updateSuccessPayment(Sponsor $sponsor, Invoice $invoice): bool
    {
        if ($sponsor->status != Support::ADVERTISE_STATUS_UNPAID) {
            return false;
        }

        if ($invoice->itemId() != $sponsor->entityId()) {
            return false;
        }

        if ($invoice->itemType() != $sponsor->entityType()) {
            return false;
        }

        if (null === $sponsor?->item) {
            return false;
        }

        $status = Support::ADVERTISE_STATUS_PENDING;

        $autoPublish = $sponsor->user->hasPermissionTo(sprintf('%s.%s', $sponsor->item->entityType(), 'auto_publish_sponsored_item'));

        if ($autoPublish) {
            $status = Support::ADVERTISE_STATUS_APPROVED;
        }

        $isPending = $status == Support::ADVERTISE_STATUS_PENDING;

        $sponsor->update([
            'status' => $status,
        ]);

        $this->clearCaches($sponsor->item, $isPending);

        if ($isPending) {
            $sponsor->toPendingItem($invoice);

            return true;
        }

        $sponsor->item->enableSponsor();

        $this->deleteUnpaidSponsors($sponsor->item);

        return true;
    }

    protected function deleteUnpaidSponsors(Content $content): void
    {
        $this->getModel()->newQuery()
            ->where([
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
                'status'    => Support::ADVERTISE_STATUS_UNPAID,
            ])
            ->get()
            ->each(function ($sponsor) {
                $sponsor->delete();
            });
    }

    public function clearCachesByEntityType(string $entityType, bool $clearPending = false): void
    {
        if ($clearPending) {
            Cache::delete(sprintf(self::PENDING_SPONSOR_CACHE_ID, $entityType));

            return;
        }

        Cache::delete(sprintf(self::APPROVED_SPONSOR_CACHE_ID, $entityType));
    }

    protected function clearCaches(Content $content, bool $clearPending = false): void
    {
        $this->clearCachesByEntityType($content->entityType(), $clearPending);
    }

    protected function createSponsorData(User $context, Content $item, float $price, string $currencyId, array $attributes): Sponsor
    {
        if ($item instanceof HasPrivacy && $item->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        $sponsor = $this->insertData($context, $item, $attributes);

        $price = Facade::calculateSponsorPrice($sponsor, $price);

        $isFree = $price == 0;

        match ($isFree) {
            true    => resolve(InvoiceRepositoryInterface::class)->createInvoiceAdminCP($context, $sponsor),
            default => resolve(InvoiceRepositoryInterface::class)->createInvoice($context, $sponsor, [
                'price'           => $price,
                'payment_gateway' => 0,
                'currency_id'     => $currencyId,
                'delay_payment'   => true,
            ]),
        };

        return $sponsor->refresh();
    }

    public function createFeedSponsor(User $context, Content $item, array $attributes): Sponsor
    {
        $currencyId = app('currency')->getUserCurrencyId($context);

        $price = app('events')->dispatch('activity.feed.get_sponsor_price', [$context, $item, $currencyId], true);

        if (null === $price) {
            throw new AuthorizationException();
        }

        return $this->createSponsorData($context, $item->activity_feed, $price, $currencyId, array_merge($attributes, [
            'sponsor_type' => Support::SPONSOR_TYPE_FEED,
        ]));
    }

    public function createSponsor(User $context, Content $item, array $attributes): Sponsor
    {
        $currencyId = app('currency')->getUserCurrencyId($context);

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($context, $item, $currencyId);

        if (null === $price) {
            throw new AuthorizationException();
        }

        return $this->createSponsorData($context, $item, $price, $currencyId, $attributes);
    }

    protected function insertData(User $context, Content $item, array $attributes = []): Sponsor
    {
        $sponsorData = $item->toSponsorData();

        $title = Arr::get($sponsorData, 'title', MetaFoxConstant::EMPTY_STRING);

        /*
         * In case create sponsor for purchasement
         */
        if (Arr::has($attributes, 'title')) {
            $title = Arr::get($attributes, 'title', $title);
        }

        if (Str::length($title) > 255) {
            $title = Str::substr($title, 0, 255);
        }

        $data = array_merge($sponsorData, $attributes, [
            'title'      => $title,
            'status'     => Support::ADVERTISE_STATUS_UNPAID,
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'item_type'  => $item->entityType(),
            'item_id'    => $item->entityId(),
            'start_date' => Arr::get($attributes, 'start_date', Carbon::now()),
        ]);

        $sponsor = $this->getModel()->newModelInstance($data);

        $sponsor->save();

        $this->addGenders($sponsor, Arr::get($attributes, 'genders'));

        $this->addLanguages($sponsor, Arr::get($attributes, 'languages'));

        $this->addLocation($sponsor, Arr::get($attributes, 'location'));

        resolve(StatisticRepositoryInterface::class)->createStatistic($sponsor);

        $sponsor->refresh();

        return $sponsor;
    }

    protected function addLocation(Sponsor $sponsor, ?array $locations): void
    {
        resolve(CountryRepositoryInterface::class)->createLocation($sponsor, $locations);
    }

    protected function addGenders(Sponsor $sponsor, ?array $genders): void
    {
        resolve(GenderRepositoryInterface::class)->addGenders($sponsor, $genders);
    }

    protected function addLanguages(Sponsor $sponsor, ?array $languages): void
    {
        resolve(LanguageRepositoryInterface::class)->addLanguages($sponsor, $languages);
    }

    public function deleteDataByItem(Content $item): void
    {
        $this->getModel()->newQuery()
            ->where([
                'item_type' => $item->entityType(),
                'item_id'   => $item->entityId(),
            ])
            ->get()
            ->each(function ($sponsor) {
                $sponsor->delete();
            });
    }

    public function deleteData(Sponsor $sponsor): void
    {
        resolve(GenderRepositoryInterface::class)->deleteGenders($sponsor);

        resolve(LanguageRepositoryInterface::class)->deleteLanguages($sponsor);

        resolve(StatisticRepositoryInterface::class)->deleteStatistic($sponsor);

        resolve(CountryRepositoryInterface::class)->deleteLocations($sponsor);

        $sponsor->unpaidInvoices()->delete();

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$sponsor]);

        $sponsor->invoices()->update(['item_deleted_title' => $sponsor->toTitle()]);
    }

    protected function getPendingItemIds(string $itemType): array
    {
        return Cache::remember(sprintf(self::PENDING_SPONSOR_CACHE_ID, $itemType), self::SPONSOR_CACHE_TIME, function () use ($itemType) {
            return $this->getModel()->newQuery()
                ->where([
                    'status'    => Support::ADVERTISE_STATUS_PENDING,
                    'item_type' => $itemType,
                ])
                ->pluck('item_id')
                ->toArray();
        });
    }

    public function isApprovedSponsor(Content $content): bool
    {
        $approvedIds = $this->getApprovedItemIdsByType($content->entityType())->pluck('item_id')->toArray();

        return in_array($content->entityId(), $approvedIds);
    }

    public function isPendingSponsor(Content $content): bool
    {
        $pendingIds = $this->getPendingItemIds($content->entityType());

        return in_array($content->entityId(), $pendingIds);
    }

    public function updateTotal(Content $content, string $type = 'total_impression'): bool
    {
        $sponsor = $this->getModel()->newQuery()
            ->with(['statistic'])
            ->where([
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
                'status'    => Support::ADVERTISE_STATUS_APPROVED,
            ])
            ->first();

        if (!$sponsor instanceof Sponsor) {
            return false;
        }

        if (null === $sponsor->statistic) {
            return false;
        }

        $sponsor->statistic->incrementAmount($type);

        resolve(ReportRepositoryInterface::class)->createReport($sponsor, $type);

        if ($type != 'total_impression') {
            return true;
        }

        $sponsor->load(['statistic']);

        if ($sponsor->total_impression == 0) {
            return true;
        }

        if ($sponsor->total_impression > $sponsor->statistic->total_impression) {
            return true;
        }

        $sponsor->update(['status' => Support::ADVERTISE_STATUS_COMPLETED, 'completed_at' => Carbon::now()]);

        if ($sponsor->item instanceof Content) {
            $sponsor->item->disableSponsor();
        }

        return true;
    }

    protected function getApprovedItemIdsByType(string $itemType): Collection
    {
        /*
         * @var Collection $sponsors
         */
        return Cache::remember(sprintf(self::APPROVED_SPONSOR_CACHE_ID, $itemType), self::SPONSOR_CACHE_TIME, function () use ($itemType) {
            return $this->getModel()->newQuery()
                ->with(['item', 'item.user'])
                ->where([
                    'status'    => Support::ADVERTISE_STATUS_APPROVED,
                    'item_type' => $itemType,
                    'is_active' => 1,
                ])
                ->get();
        });
    }

    public function getSponsoredItemIdsByType(User $user, string $itemType, ?int $limit = null, ?array $loadedItemIds = null, bool $shuffle = false): array
    {
        $sponsors = $this->getApprovedItemIdsByType($itemType);

        if (!$sponsors->count()) {
            return [];
        }

        if (is_array($loadedItemIds) && count($loadedItemIds)) {
            $sponsors = $sponsors->filter(function ($sponsor) use ($loadedItemIds) {
                return !in_array($sponsor->itemId(), $loadedItemIds);
            });
        }

        if ($shuffle) {
            $sponsors = $sponsors->shuffle();
        }

        $itemIds = [];

        /**
         * @var FilterConditionServiceInterface $filterService
         */
        $filterService = resolve(FilterConditionServiceInterface::class);

        foreach ($sponsors as $sponsor) {
            if (!$filterService->filterByUserInformation($user, $sponsor)) {
                continue;
            }

            if (!$filterService->filterByUserLocation($user, $sponsor)) {
                continue;
            }

            if (!$filterService->filterByDate($sponsor)) {
                continue;
            }

            if (null === $sponsor->item) {
                continue;
            }

            if (!$filterService->filterBlocked($user, $sponsor->item->user)) {
                continue;
            }

            $itemIds[] = $sponsor->itemId();

            if (null === $limit) {
                continue;
            }

            if (count($itemIds) >= $limit) {
                break;
            }
        }

        return $itemIds;
    }

    protected function filterByDate(FilterConditionServiceInterface $filterService, Sponsor $sponsor): bool
    {
        if ($filterService->filterByDate($sponsor)) {
            return true;
        }

        if (null === $sponsor->end_date) {
            return false;
        }

        $endDate = Carbon::parse($sponsor->end_date);

        $now = Carbon::now();

        if ($endDate->lessThanOrEqualTo($now) && $sponsor->item instanceof Content) {
            $sponsor->item->disableSponsor();
            $this->clearCaches($sponsor->item);
        }

        return false;
    }

    public function approveSponsor(User $user, Sponsor $sponsor): bool
    {
        $sponsor->update(['status' => Support::ADVERTISE_STATUS_APPROVED]);

        if ($sponsor->item instanceof Content) {
            $sponsor->item->enableSponsor();

            $this->clearCaches($sponsor->item, true);
        }

        if ($user->entityId() != $sponsor->userId()) {
            $this->sendApprovedNotification($user, $sponsor->user, $sponsor);
        }

        return true;
    }

    protected function sendApprovedNotification(User $context, ?User $notifiable, Sponsor $sponsor): void
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new SponsorApprovedNotification($sponsor);

        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    public function denySponsor(User $user, Sponsor $sponsor): bool
    {
        $sponsor->update(['status' => Support::ADVERTISE_STATUS_DENIED]);

        if ($sponsor->item instanceof Content) {
            $this->clearCaches($sponsor->item, true);
        }

        if ($user->entityId() != $sponsor->userId()) {
            $this->sendDeniedNotification($user, $sponsor->user, $sponsor);
        }

        return true;
    }

    protected function sendDeniedNotification(User $context, ?User $notifiable, Sponsor $sponsor): void
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new SponsorDeniedNotification($sponsor);

        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    public function updateSponsor(User $user, Sponsor $sponsor, array $attributes): Sponsor
    {
        $sponsor->fill($attributes);

        $sponsor->save();

        $this->addGenders($sponsor, Arr::get($attributes, 'genders'));

        $this->addLanguages($sponsor, Arr::get($attributes, 'languages'));

        $this->addLocation($sponsor, Arr::get($attributes, 'location'));

        $sponsor->refresh();

        return $sponsor;
    }

    public function sponsorFeed(User $context, Content $item): Sponsor
    {
        $sponsor = $this->insertData($context, $item->activity_feed, [
            'sponsor_type' => Support::SPONSOR_TYPE_FEED,
        ]);

        resolve(InvoiceRepositoryInterface::class)->createInvoiceAdminCP($context, $sponsor);

        return $sponsor;
    }

    public function deleteSponsor(Sponsor $sponsor): bool
    {
        $sponsor->delete();

        if ($sponsor->item instanceof Content) {
            $sponsor->item->disableSponsor();
        }

        return true;
    }

    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewSponsors(User $user, array $attributes = []): Paginator
    {
        $startFrom   = Arr::get($attributes, 'start_date');
        $startTo     = Arr::get($attributes, 'end_date');
        $status      = Arr::get($attributes, 'status');

        $query = $this->getModel()->newQuery()
            ->where('advertise_sponsors.user_id', '=', $user->entityId());

        $this->buildDateCondition($query, 'start_date', $startFrom, $startTo);

        if ($status) {
            $query->addScope(new StatusScope($status));
        }

        return $query
            ->orderByDesc('advertise_sponsors.id')
            ->paginate(Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE), ['advertise_sponsors.*']);
    }

    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAdminCP(array $attributes = []): Paginator
    {
        $startFrom         = Arr::get($attributes, 'start_date');
        $startTo           = Arr::get($attributes, 'end_date');
        $title             = Arr::get($attributes, 'title');
        $userFullName      = Arr::get($attributes, 'full_name');
        $status            = Arr::get($attributes, 'status');
        $sponsorType       = Arr::get($attributes, 'sponsor_type');
        $active            = Arr::get($attributes, 'is_active');
        $limit             = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery()
            ->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'advertise_sponsors.user_id');
            });

        $this->buildDateCondition($query, 'start_date', $startFrom, $startTo);

        if (is_string($title) && MetaFoxConstant::EMPTY_STRING != $title) {
            $title = trim($title);
            if ($title) {
                $query->where('advertise_sponsors.title', $this->likeOperator(), '%' . $title . '%');
            }
        }

        if (is_string($userFullName) && MetaFoxConstant::EMPTY_STRING != $userFullName) {
            $userFullName = trim($userFullName);
            if ($userFullName) {
                $query->where('user_entities.name', $this->likeOperator(), '%' . $userFullName . '%');
            }
        }

        if (null !== $active) {
            $query->where('advertise_sponsors.is_active', '=', (int) $active);
        }

        if (null !== $sponsorType) {
            $query->where('advertise_sponsors.sponsor_type', '=', $sponsorType);
        }

        if (null !== $status) {
            $query->addScope(new StatusScope($status));
        }

        return $query
            ->orderByDesc('advertise_sponsors.id')
            ->paginate($limit, ['advertise_sponsors.*']);
    }

    protected function buildDateCondition(Builder $query, string $field, ?string $from, ?string $to): void
    {
        if (!$from && !$to) {
            return;
        }

        $field = sprintf('advertise_sponsors.%s', $field);

        $query->whereNotNull($field);

        if ($from) {
            $query->where($field, '>=', $from);
        }

        if ($to) {
            $query->where($field, '<=', $to);
        }
    }

    /**
     * @param  Sponsor $sponsor
     * @param  bool    $isActive
     * @return bool
     */
    public function activeSponsor(Sponsor $sponsor, bool $isActive): bool
    {
        $sponsor->update(['is_active' => $isActive]);

        if ($sponsor->is_approved && $sponsor->item instanceof Content) {
            $this->clearCaches($sponsor->item);
        }

        return true;
    }

    public function markAsPaid(User $context, Sponsor $sponsor): bool
    {
        $user = $sponsor->user;

        if (!$user instanceof User) {
            $user = $context;
        }

        resolve(InvoiceRepositoryInterface::class)->createInvoiceAdminCP($user, $sponsor);

        if ($user->entityId() != $context->entityId()) {
            $this->sendMarkAsPaidNotification($context, $user, $sponsor);
        }

        return true;
    }

    protected function sendMarkAsPaidNotification(User $context, ?User $notifiable, Sponsor $sponsor)
    {
        if (null === $notifiable) {
            return;
        }

        $notification = new MarkSponsorAsPaidNotification($sponsor);

        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }
}
