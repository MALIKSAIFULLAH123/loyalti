<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MetaFox\ActivityPoint\Models\ActionType;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\ActivityPoint\Notifications\PurchasePackageFailedNotification;
use MetaFox\ActivityPoint\Notifications\PurchasePackageSuccessNotification;
use MetaFox\ActivityPoint\Policies\PackagePolicy;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint as ActivityPointSupport;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;
use MetaFox\User\Models\User as UserModel;

/**
 * Class PointPackageRepository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
class PointPackageRepository extends AbstractRepository implements PointPackageRepositoryInterface
{
    use InputCleanerTrait;

    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewPackages(User $context, array $attributes): Paginator
    {
        policy_authorize(PackagePolicy::class, 'viewAny', $context);

        $sort     = $attributes['sort'];
        $sortType = $attributes['sort_type'];
        $search   = $attributes['q'] ?? '';

        $query     = $this->getModel()->newModelQuery();
        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        return $query
            ->addScope($sortScope)
            ->where('is_active', '=', 1)
            ->paginate($attributes['limit']);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewPackage(User $context, int $id): Model
    {
        $package = $this->find($id);

        policy_authorize(PackagePolicy::class, 'view', $context);

        return $package;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function purchasePackage(User $context, int $id, array $attributes, array $extra = []): array
    {
        $package = $this->find($id);

        policy_authorize(PackagePolicy::class, 'purchase', $context, $package);

        $purchase = $this->initPurchase($context, $package, $attributes);

        $returnUrl = url_utility()->makeApiFullUrl('activitypoint/transactions-package');

        // Init order them place order
        $order  = Payment::initOrder($purchase);
        $params = [
            'return_url'  => $returnUrl,
            'cancel_url'  => $returnUrl,
            'description' => $purchase->payment_description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        $data = Payment::placeOrder($order, $purchase->gateway_id, $params);

        return [
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ];
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function viewPackagesAdmin(User $context, array $attributes): Collection
    {
        $sort     = $attributes['sort'];
        $sortType = $attributes['sort_type'];
        $search   = $attributes['q'] ?? '';

        $query     = $this->getModel()->newModelQuery();
        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        return $query
            ->addScope($sortScope)
            ->get();
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function createPackage(User $context, array $attributes): Model
    {
        policy_authorize(PackagePolicy::class, 'create', $context);

        if ($attributes['temp_file'] > 0) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        $attributes['title'] = $this->cleanTitle($attributes['title']);

        $package = new Model();
        $package->fill($attributes);
        $package->save();

        return $package;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function updatePackage(User $context, int $id, array $attributes): Model
    {
        /** @var Model $package */
        $package = $this->find($id);

        policy_authorize(PackagePolicy::class, 'update', $context, $package);

        $removeFile = false;

        $tempFile = (int) Arr::get($attributes, 'temp_file', 0);

        if ($tempFile > 0) {
            $removeFile = true;
        }

        if (Arr::get($attributes, 'file.status') == MetaFoxConstant::FILE_REMOVE_STATUS) {
            $removeFile = true;
        }

        if ($removeFile) {
            if ($package->image_file_id) {
                app('storage')->deleteFile($package->image_file_id, null);
            }
            Arr::set($attributes, 'image_file_id', null);
        }

        if ($tempFile > 0) {
            $attributes['image_file_id'] = upload()->getFileId($attributes['temp_file'], true);
        }

        $package->fill($attributes);

        $package->save();

        return $package->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deletePackage(User $context, int $id): bool
    {
        /** @var Model $package */
        $package = $this->find($id);
        policy_authorize(PackagePolicy::class, 'delete', $context, $package);

        return (bool) $this->delete($id);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function activatePackage(User $context, int $id): Model
    {
        /** @var Model $package */
        $package = $this->find($id);
        policy_authorize(PackagePolicy::class, 'update', $context, $package);

        $package->update(['is_active' => 1]);

        return $package->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deactivatePackage(User $context, int $id): Model
    {
        /** @var Model $package */
        $package = $this->find($id);
        policy_authorize(PackagePolicy::class, 'update', $context, $package);

        $package->update(['is_active' => 0]);

        return $package->refresh();
    }

    /**
     * @param User                 $context
     * @param Model                $package
     * @param array<string, mixed> $attributes
     *
     * @return PackagePurchase
     */
    public function initPurchase(User $context, Model $package, array $attributes): PackagePurchase
    {
        $userCurrency = app('currency')->getUserCurrencyId($context);

        $params = [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'package_id'  => $package->entityId(),
            'status'      => PackagePurchase::STATUS_INIT,
            'price'       => Arr::get($package->price, $userCurrency, Arr::first($package->price)),
            'currency_id' => $userCurrency,
            'gateway_id'  => Arr::get($attributes, 'payment_gateway', null),
            'points'      => $package->amount,
        ];

        $purchase = $this->getPurchasePackageRepository()->getModel();
        $purchase->fill($params);
        $purchase->save();

        return $purchase;
    }

    /**
     * @inheritDoc
     */
    public function onSuccessPurchasePackage(PackagePurchase $purchase, ?string $transactionId = null): void
    {
        $user    = $purchase->user;
        $package = $purchase->package;

        if (!$user instanceof User) {
            return;
        }

        if (!$package instanceof Model) {
            return;
        }

        // Add the amount to user's total points
        $extra = [
            'module_id'        => 'activitypoint',
            'package_id'       => 'metafox/activity-point',
            'action'           => 'activitypoint::phrase.context_bought_a_point_package',
            'action_type_name' => ActionType::ACTIVITYPOINT_BUY_A_POINT_PACKAGE_TYPE,
            'action_params'    => [
                'is_self'   => 1,
                'user_name' => $user instanceof UserModel ? $user->display_name : '',
            ],
        ];

        ActivityPoint::addPoints($user, $user, $package->amount, ActivityPointSupport::TYPE_BOUGHT, $extra);

        $package->incrementTotalPurchase();

        $purchase->update(['status' => PackagePurchase::STATUS_SUCCESS, 'transaction_id' => $transactionId]);

        Notification::send($user, new PurchasePackageSuccessNotification($purchase));
    }

    /**
     * @inheritDoc
     */
    public function onFailedPurchasePackage(PackagePurchase $purchase): void
    {
        $user    = $purchase->user;
        $package = $purchase->package;

        if (!$user instanceof User) {
            return;
        }

        if (!$package instanceof Model) {
            return;
        }

        $purchase->update(['status' => PackagePurchase::STATUS_FAILED]);

        Notification::send($user, new PurchasePackageFailedNotification($purchase));
    }

    protected function getPurchasePackageRepository(): PurchasePackageRepositoryInterface
    {
        return resolve(PurchasePackageRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getPurchasePackageMessage(int $id): array
    {
        $initData = [
            'init', //status default
            MetaFoxConstant::EMPTY_STRING, //message default
        ];

        try {
            $purchase = $this->getPurchasePackageRepository()->find($id);

            return match ($purchase->status) {
                PackagePurchase::STATUS_SUCCESS => [
                    'success',
                    __p('activitypoint::phrase.your_point_are_updated'),
                ],
                PackagePurchase::STATUS_FAILED  => [
                    'failed',
                    __p('activitypoint::phrase.purchase_package_fail'),
                ],
                default                         => [
                    'init',
                    __p('activitypoint::phrase.purchase_is_being_processed'),
                ],
            };
        } catch (Exception $e) {
            Log::info("No purchase with ID:  $id found");
            Log::info($e->getMessage());
        }

        return $initData;
    }

    public function getSearchOptions(): array
    {
        return $this->getModel()->newQuery()
            ->get()
            ->map(function ($package) {
                return [
                    'label' => $package->toTitle(),
                    'value' => $package->entityId(),
                ];
            })
            ->toArray();
    }
}
