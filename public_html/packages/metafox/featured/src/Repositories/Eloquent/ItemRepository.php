<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Jobs\DeleteItemForDeletedUserJob;
use MetaFox\Featured\Jobs\HandleItemForDeletedContentJob;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Models\Package;
use MetaFox\Featured\Notifications\CancelledFeaturedItemForDeletedContentNotification;
use MetaFox\Featured\Notifications\CancelledFeaturedItemNotification;
use MetaFox\Featured\Notifications\EndedFeaturedItemNotification;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ItemRepository.
 */
class ItemRepository extends AbstractRepository implements ItemRepositoryInterface
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected InvoiceRepositoryInterface $invoiceRepository;

    /**
     * @var PackageRepositoryInterface
     */
    protected PackageRepositoryInterface $packageRepository;

    public function boot()
    {
        parent::boot();

        $this->invoiceRepository = resolve(InvoiceRepositoryInterface::class);
        $this->packageRepository = resolve(PackageRepositoryInterface::class);
    }

    public function model()
    {
        return Item::class;
    }

    public function isFeaturedByUser(User $user, Content $content): bool
    {
        if (!$content->is_featured) {
            return false;
        }

        $featuredItem = $this->getModel()->newQuery()
            ->where([
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
                'user_id'   => $user->entityId(),
                'status'    => Constants::FEATURED_ITEM_STATUS_RUNNING,
            ])
            ->orderByDesc('id')
            ->first();

        if (!$featuredItem instanceof Item) {
            return false;
        }

        return true;
    }

    public function createItemForFree(User $user, Content $content): Item
    {
        /*
         * Cancel all featured items which created from this user
         */
        $this->getModel()->newQuery()
            ->where([
                'user_id'   => $user->entityId(),
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
            ])
            ->whereIn('status', [
                Constants::FEATURED_ITEM_STATUS_UNPAID,
                Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT,
                Constants::FEATURED_ITEM_STATUS_RUNNING])
            ->update(['status' => Feature::getCancelledPaymentStatus()]);

        $attributes = [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $content->entityType(),
            'item_id'   => $content->entityId(),
            'status'    => Constants::FEATURED_ITEM_STATUS_RUNNING,
        ];

        /**
         * @var Item $item
         */
        $item = $this->getModel()->newInstance($attributes);

        $item->save();

        $item->refresh();

        $this->invoiceRepository->createInvoiceForFree($user, $content, array_merge($attributes, [
            'featured_id' => $item->entityId(),
        ]));

        return $item;
    }

    public function createItem(User $user, Content $content, array $attributes): Item
    {
        /**
         * @var Package $package
         */
        $package = $this->getPackage(Arr::get($attributes, 'package_id'));

        /**
         * @var Item $item
         */
        $item = $this->getModel()->newInstance(array_merge($attributes, [
            'user_id'                 => $user->entityId(),
            'user_type'               => $user->entityType(),
            'item_type'               => $content->entityType(),
            'item_id'                 => $content->entityId(),
            'status'                  => Constants::FEATURED_ITEM_STATUS_UNPAID,
            'package_duration_period' => $package->duration_period,
            'package_duration_value'  => $package->duration_value,
        ]));

        $item->save();

        $item->refresh();

        $this->invoiceRepository->createInvoice($user, $content, array_merge($attributes, [
            'featured_id' => $item->entityId(),
        ]));

        return $item->refresh();
    }

    public function getPackage(int $packageId): Package
    {
        $package = $this->packageRepository->getPackageById($packageId);

        if (null === $package) {
            throw new ModelNotFoundException(__p('featured::validation.package_not_found'), 404);
        }

        return $package;
    }

    public function validatePackage(Content $content, Package $package): void
    {
        if ($package->applicable_item_type !== Constants::ITEM_APPLICABLE_SCOPE_SPECIFIC) {
            return;
        }

        $allowItemTypes = $package->item_types->pluck('item_type')->toArray();

        if (in_array($content->entityType(), $allowItemTypes)) {
            return;
        }

        throw new ModelNotFoundException(__p('featured::validation.package_not_found'), 404);
    }

    public function markItemPendingPayment(Item $item): bool
    {
        if ($item->status != Constants::FEATURED_ITEM_STATUS_UNPAID) {
            return false;
        }

        $item->update(['status' => Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT]);

        return true;
    }

    public function markItemRunning(Item $item): bool
    {
        if (!in_array($item->status, [Constants::FEATURED_ITEM_STATUS_UNPAID, Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT])) {
            return false;
        }

        $item->update([
            'status'     => Constants::FEATURED_ITEM_STATUS_RUNNING,
            'expired_at' => Feature::getExpiredDatetimeByDuration($item->package_duration_period, $item->package_duration_value),
        ]);

        return true;
    }

    public function deleteItem(Item $item): bool
    {
        $item->delete();

        return true;
    }

    public function handleCancellingRunningItemBySystem(Item $item): bool
    {
        if (!$item->item instanceof Content) {
            return false;
        }

        Feature::increasePackageTotalCancelled($item->package_id);

        if ($item->is_running) {
            Feature::deactivateItemFeatured($item->item);
        }

        return true;
    }

    /**
     * @param User    $user
     * @param Content $content
     *
     * @return bool
     */
    public function markItemCancelledByUnFeaturingContent(User $user, Content $content): bool
    {
        $item = $this->getModel()->newQuery()
            ->where([
                'item_id'   => $content->entityId(),
                'item_type' => $content->entityType(),
            ])
            ->whereIn('status', [Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT, Constants::FEATURED_ITEM_STATUS_RUNNING])
            ->orderByDesc('id')
            ->first();

        if (!$item instanceof Item) {
            return false;
        }

        return $this->markItemCancelled($user, $item);
    }

    public function markItemCancelled(User $user, Item $item): bool
    {
        $item->update(['status' => Feature::getCancelledPaymentStatus()]);

        Feature::increasePackageTotalCancelled($item->package_id);

        Feature::deactivateItemFeatured($item->item);

        if ($item->unpaidInvoice instanceof Invoice) {
            $this->invoiceRepository->cancelInvoice($user, $item->unpaidInvoice);
        }

        $this->sendCancelledNotification($user, $item);

        return true;
    }

    public function markItemEnded(Item $item): bool
    {
        $item->update(['status' => Constants::FEATURED_ITEM_STATUS_ENDED]);

        Feature::increasePackageTotalEnd($item->package_id);

        Feature::deactivateItemFeatured($item->item);

        $this->sendEndedNotification($item);

        return true;
    }

    protected function sendEndedNotification(Item $item): void
    {
        if (null === $item->user) {
            return;
        }

        $notification = new EndedFeaturedItemNotification($item);

        $params = [$item->user, $notification];

        Notification::send(...$params);
    }

    protected function sendCancelledNotificationForDeletedContent(Item $item): void
    {
        if (null === $item->user) {
            return;
        }

        $notification = new CancelledFeaturedItemForDeletedContentNotification($item);

        $params = [$item->user, $notification];

        Notification::send(...$params);
    }

    protected function sendCancelledNotification(User $user, Item $item): void
    {
        if (null === $item->user) {
            return;
        }

        if ($user->entityId() === $item->userId()) {
            return;
        }

        $notification = new CancelledFeaturedItemNotification($item);

        $notification->setSender($user);

        $params = [$item->user, $notification];

        Notification::send(...$params);
    }

    public function deleteUserData(User $user): void
    {
        DeleteItemForDeletedUserJob::dispatch($user->entityId());
    }

    public function handleContentDeleted(Content $content): bool
    {
        HandleItemForDeletedContentJob::dispatch($content->entityType(), $content->entityId(), Feature::getItemTitle($content));

        return true;
    }

    public function markItemCancelledForDeletedContent(Item $item): bool
    {
        $item->update(['status' => Feature::getCancelledPaymentStatus()]);

        Feature::increasePackageTotalCancelled($item->package_id);

        $this->sendCancelledNotificationForDeletedContent($item);

        return true;
    }

    public function isContentAvailableForFeature(Content $content): bool
    {
        if ($content->is_featured) {
            return false;
        }

        $exists = Item::query()
            ->where([
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
            ])
            ->whereIn('status', [Constants::FEATURED_ITEM_STATUS_RUNNING, Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT])
            ->orderByDesc('id')
            ->exists();

        if ($exists) {
            return false;
        }

        return true;
    }

    public function viewItems(User $context, array $attributes = []): Paginator
    {
        $id         = Arr::get($attributes, 'id');
        $fromDate   = Arr::get($attributes, 'from_date');
        $toDate     = Arr::get($attributes, 'to_date');
        $limit      = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $duration   = Arr::get($attributes, 'package_duration_period');
        $pricing    = Arr::get($attributes, 'pricing');
        $attributes = Arr::only($attributes, ['item_type', 'package_id', 'status']);

        $builder = $this->getModel()->newQuery()
            ->where([
                'user_id' => $context->entityId(),
            ]);

        if (count($attributes) > 0) {
            $builder->where($attributes);
        }

        if (is_string($pricing)) {
            match ($pricing) {
                Constants::PRICING_OPTION_FREE    => $builder->where('is_free', '=', true),
                Constants::PRICING_OPTION_CHARGED => $builder->where('is_free', '=', false),
                default                           => null,
            };
        }

        if (is_string($duration)) {
            match ($duration) {
                Constants::DURATION_ENDLESS => $builder->whereNull('package_duration_period'),
                default                     => $builder->where('package_duration_period', '=', $duration),
            };
        }

        $whenScope = new WhenScope();
        $whenScope->setWhen(Browse::WHEN_BETWEEN);

        if (is_string($fromDate)) {
            $whenScope->setFromColumn('featured_items.created_at');
            $whenScope->setFromDate($fromDate);
        }

        if (is_string($toDate)) {
            $whenScope->setToColumn('featured_items.created_at');
            $whenScope->setToDate($toDate);
        }

        $builder->addScope($whenScope);

        if (is_numeric($id)) {
            $builder->where('id', '=', $id);
        }

        return $builder->with(['item', 'package'])
            ->orderByDesc('id')
            ->paginate($limit, ['featured_items.*']);
    }

    public function setInvoiceRepository(InvoiceRepositoryInterface $invoiceRepository): self
    {
        $this->invoiceRepository = $invoiceRepository;

        return $this;
    }

    public function setPackageRepository(PackageRepositoryInterface $packageRepository): self
    {
        $this->packageRepository = $packageRepository;

        return $this;
    }

    public function markItemFree(Item $item): bool
    {
        $item->update(['is_free' => true]);

        return true;
    }
}
