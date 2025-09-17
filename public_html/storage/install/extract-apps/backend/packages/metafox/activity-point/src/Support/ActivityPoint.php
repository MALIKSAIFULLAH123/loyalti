<?php

namespace MetaFox\ActivityPoint\Support;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\ActivityPoint\Models\ActionType;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\ActivityPoint\Models\PointSetting as PointSetting;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\ActivityPoint\Models\PointTransaction as Transaction;
use MetaFox\ActivityPoint\Notifications\AdjustPointsNotification;
use MetaFox\ActivityPoint\Notifications\ReceivedGiftedPointsNotification;
use MetaFox\ActivityPoint\Policies\StatisticPolicy;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\ActionType as ActionTypeFacade;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants as FormConstants;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\UserValue;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityPoint implements \MetaFox\ActivityPoint\Contracts\Support\ActivityPoint
{
    public const TOTAL_POINT_VALUE_NAME = 'total_activity_points';

    public const TYPE_ALL       = 0;
    public const TYPE_EARNED    = 1;
    public const TYPE_BOUGHT    = 2;
    public const TYPE_SENT      = 3;
    public const TYPE_SPENT     = 4;
    public const TYPE_RECEIVED  = 5;
    public const TYPE_RETRIEVED = 6;

    public const SUBTRACTED_COLOR = '#f02848';
    public const ADDED_COLOR      = '#31a24a';

    public const ALLOW_TYPES = [
        'activitypoint::phrase.type_all'       => self::TYPE_ALL,
        'activitypoint::phrase.type_earned'    => self::TYPE_EARNED,
        'activitypoint::phrase.type_bought'    => self::TYPE_BOUGHT,
        'activitypoint::phrase.type_sent'      => self::TYPE_SENT,
        'activitypoint::phrase.type_spent'     => self::TYPE_SPENT,
        'activitypoint::phrase.type_received'  => self::TYPE_RECEIVED,
        'activitypoint::phrase.type_retrieved' => self::TYPE_RETRIEVED,
    ];

    private PointStatisticRepositoryInterface $statisticRepository;

    private PointSettingRepositoryInterface $settingRepository;

    private PointTransactionRepositoryInterface $transactionRepository;

    public function __construct(
        PointStatisticRepositoryInterface   $statisticRepository,
        PointSettingRepositoryInterface     $settingRepository,
        PointTransactionRepositoryInterface $transactionRepository,
    )
    {
        $this->statisticRepository   = $statisticRepository;
        $this->settingRepository     = $settingRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getUserPointBalance(User $context): int
    {
        $points = $this->getTotalActivityPoints($context);

        if (0 == $points) {
            return 0;
        }

        $pendingConversionPoints = PointConversion::getPendingConversionPoints($context);

        if (0 == $pendingConversionPoints) {
            return $points;
        }

        if ($points < $pendingConversionPoints) {
            return 0;
        }

        return $points - $pendingConversionPoints;
    }

    /**
     * @inheritDoc
     */
    public function getTotalActivityPoints(User $context): int
    {
        return (int) UserValue::getUserValueSettingByName($context, self::TOTAL_POINT_VALUE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function updateActivityPoints(
        User $context,
        int  $amount,
    ): bool
    {
        return UserValue::updateUserValueSetting($context, [
            self::TOTAL_POINT_VALUE_NAME => $amount,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function addPoints(User $context, User $owner, int $amount, ?int $type = null, array $extra = []): int
    {
        $currentAmount = $this->getTotalActivityPoints($context);

        $isUpdated = $this->updateActivityPoints($context, max($currentAmount + $amount, 0));

        if ($isUpdated) {
            app('events')->dispatch(
                'activitypoint.point_updated',
                [$context, $owner, $type, $amount, $extra],
            );
        }

        // Create a new transaction
        $params = array_merge([
            'type'   => $type ?? 0,
            'points' => $amount,
        ], $extra);

        $this->createTransaction($context, $owner, $params);

        return $this->getUserPointBalance($context);
    }

    public function updateStatistic(User $context, int $type, int $amount): PointStatistic
    {
        return $this->statisticRepository->updateStatistic($context, $type, ['amount' => $amount]);
    }

    /**
     * @param User $user
     *
     * @inheritDoc
     */
    public function updateUserPoints(User $user, Entity $content, string $action, int $type, bool $allowMultiTransactionsPerItem = false): int
    {
        $setting = $this->settingRepository->getUserPointSetting($user, $content, $action, $type);

        if (!$setting instanceof PointSetting) {
            return 0;
        }

        $pointsSetting = $setting->points;

        if (!$allowMultiTransactionsPerItem) {
            $transactionParams = [
                'point_setting_id' => $setting->entityId(),
            ];

            // Get existed transactions on the $content
            $transaction = $this->transactionRepository->getTransactionByItem($user, $content, $transactionParams);

            /**
             * If you deleted item, make sure that added point transaction was existed
             * If you add new item, make sure that added point once.
             */
            $granted = match ($type) {
                self::TYPE_RETRIEVED => $transaction instanceof Transaction,
                default              => null === $transaction,
            };

            if (!$granted) {
                return 0;
            }

            if ($transaction instanceof Transaction) {
                $pointsSetting = match ($type) {
                    self::TYPE_RETRIEVED => abs($transaction->points),
                    default              => 0
                };
            }
        }

        if (!$pointsSetting) {
            return 0;
        }

        $actionPhrase = match ($type) {
            self::TYPE_RETRIEVED => 'activitypoint::phrase.system_retrieves_points',
            default              => str_replace('description', 'action', $setting->description_phrase),
        };

        $extra = [
            'module_id'        => $setting->module_id,
            'package_id'       => $setting->package_id,
            'action'           => $actionPhrase,
            'point_setting_id' => $setting->entityId(),
            'item_id'          => $content->entityId(),
            'item_type'        => $content->entityType(),
        ];

        $actionType = ActionTypeFacade::getActionType($content, $action);
        if ($actionType instanceof ActionType) {
            $extra['action_type_name'] = $actionType->name;
        }

        match ($type) {
            self::TYPE_EARNED    => $this->addPoints($user, $user, $pointsSetting, $type, $extra),
            self::TYPE_RETRIEVED => $this->addPoints($user, $user, (0 - $pointsSetting), $type, $extra),
            default              => 0,
        };

        return $pointsSetting;
    }

    /**
     * @inheritDoc
     */
    public function createTransaction(User $user, User $owner, array $data): Transaction
    {
        return $this->transactionRepository->createTransaction($user, $owner, $data);
    }

    /**
     * @inheritDoc
     */
    public function adjustPoints(User $context, User $user, int $type, int $amount): ?int
    {
        $canSendPoint = $this->checkAdminCanSendPoint($context, $user, $type, $amount);

        if (!$canSendPoint) {
            return null;
        }

        $fullName = $context instanceof UserModel ? $context->display_name : '';

        $action = match ($type) {
            self::TYPE_RETRIEVED => 'activitypoint::phrase.your_point_has_been_revoked_by_the_administrator',
            self::TYPE_RECEIVED  => 'activitypoint::phrase.context_get_points_from',
            default              => '',
        };

        $actionTypeName = match ($type) {
            self::TYPE_RETRIEVED => ActionType::ACTIVITYPOINT_POINT_REVOCATION_TYPE,
            self::TYPE_RECEIVED  => ActionType::ACTIVITYPOINT_RECEIVE_POINTS_TYPE,
            default              => '',
        };

        $actionParams = match ($type) {
            self::TYPE_RECEIVED => [
                'user'      => $fullName,
                'url'       => $context->toUrl(),
                'is_self'   => (int) ($context->entityId() == $user->entityId()),
                'user_name' => $user instanceof UserModel ? $user->display_name : '',
            ],
            default             => [],
        };

        $extra = [
            'action'           => $action,
            'action_type_name' => $actionTypeName,
            'action_params'    => $actionParams,
            'module_id'        => 'activitypoint',
        ];

        if ($type == self::TYPE_RECEIVED) {
            $extra['is_admincp'] = 1;
        }

        $points = match ($type) {
            self::TYPE_RETRIEVED => $this->addPoints($user, $user, 0 - $amount, $type, $extra),
            self::TYPE_RECEIVED  => $this->addPoints($user, $user, $amount, $type, $extra),
            default              => 0,
        };

        $handler = new AdjustPointsNotification($user);

        $handler->setData([
            'points' => $amount,
            'type'   => $type,
        ]);

        Notification::send($user, $handler);

        return $points;
    }

    private function checkAdminCanSendPoint(User $context, User $user, int $type, int $amount): bool
    {
        $limit  = (int) $context->getPermissionValue('activitypoint.maximum_activity_points_admin_can_adjust');
        $period = (int) $context->getPermissionValue('activitypoint.period_time_admin_adjust_activity_points');

        $currentUserPoint = UserValue::getUserValueSettingByName($user, 'total_activity_points') ?? 0;

        if ($type == self::TYPE_RETRIEVED && $currentUserPoint < $amount) {
            return false;
        }

        if ($type == self::TYPE_RECEIVED) {
            if ($amount + $currentUserPoint > 2000000000) { // The maximum number of points that each user on the system can have is 2 billion
                abort(422, __p('activitypoint::phrase.the_maximum_number_of_points_user_can_have'));
            }
        }

        if (0 == $limit) {
            return true;
        }

        if ($context->hasSuperAdminRole()) {
            return true;
        }

        if ($context->hasAdminRole()) {
            return true;
        }

        if ($amount > $limit) {
            return false;
        }

        $sentPoint = $this->getSentPoint($period);

        if ($sentPoint >= $limit) {
            return false;
        }

        return true;
    }

    private function getSentPoint(int $period): int
    {
        $time = $this->getTimeByPeriod($period);

        return $this->transactionRepository->getAdminSentPointByTime($time);
    }

    private function getTimeByPeriod(int $period): string
    {
        return match ($period) {
            1 => Carbon::now()->startOfDay(),
            2 => Carbon::now()->subWeek()->startOfDay(),
            3 => Carbon::now()->subMonth()->startOfDay(),
            4 => Carbon::now()->subYear()->startOfDay(),
        };
    }

    /**
     * @inheritDoc
     */
    public function proceedPayment(Order $order): bool
    {
        $user = $order->user;
        $data = collect($order->toGatewayOrder());
        if (!$user instanceof User) {
            return false;
        }

        // Amount of point is based on the conversion rate
        $currency      = Arr::get($data, 'currency') ?? app('currency')->getUserCurrencyId($user);
        $total         = Arr::get($data, 'total') ?? 0;
        $amount        = $this->convertPointFromPrice($currency, $total);
        $currentPoints = $this->getUserPointBalance($user);

        if ($amount == -1 || $currentPoints < $amount) {
            $message = json_encode([
                'title'   => __p('activitypoint::phrase.oops_payment_failed'),
                'message' => __p('activitypoint::phrase.not_enough_point_to_pay'),
            ]);
            abort(403, $message ?? '');
        }

        [, , , $packageId] = app('core.drivers')->loadDriver(
            Constants::DRIVER_TYPE_ENTITY,
            Arr::get($data, 'item_type')
        );

        $extra = [
            'module_id'        => PackageManager::getAlias($packageId),
            'package_id'       => $packageId,
            'action'           => 'activitypoint::phrase.spent_activity_point_action',
            'action_type_name' => ActionType::ACTIVITYPOINT_SPEND_POINTS_TYPE,
        ];

        $this->addPoints($user, $user, 0 - $amount, self::TYPE_SPENT, $extra);

        $item = $order->item;
        if ($item instanceof IsBillable) {
            $owner = $item->payee();

            if ($owner instanceof User) {
                $this->addPointForOwnerItem($user, $owner, $packageId, $amount);
            }
        }

        return true;
    }

    protected function addPointForOwnerItem(User $user, User $owner, string $packageId, int $amount): void
    {
        if ($owner->hasSuperAdminRole()) {
            return;
        }

        //owner received
        $receivedActionParams = [
            'user' => $user instanceof UserModel ? $user->display_name : '',
            'url'  => $user instanceof UserModel ? $user->toLink() : '',
        ];

        $receivedExtra = [
            'action'           => 'activitypoint::phrase.users_used_points_to_purchase_your_item',
            'action_type_name' => ActionType::ACTIVITYPOINT_RECEIVE_POINTS_FROM_SELLING_ITEMS_TYPE,
            'module_id'        => PackageManager::getAlias($packageId),
            'package_id'       => $packageId,
            'action_params'    => $receivedActionParams,
        ];

        $this->addPoints($owner, $owner, $amount, self::TYPE_RECEIVED, $receivedExtra);
    }

    /**
     * @inheritDoc
     */
    public function convertPointFromPrice(string $currency, float $amount): int
    {
        $conversionRate = $this->getConversionRate($currency);

        if ($conversionRate <= 0) {
            return -1;
        }

        $final = round($amount / $conversionRate, 2);

        if ($final > 0 && $final < 1) {
            return -1;
        }

        return (int) round($final);
    }

    /**
     * @inheritDoc
     */
    public function getConversionRate(string $currency): float
    {
        return Settings::get('activitypoint.conversion_rate.' . $currency, 0);
    }

    /**
     * @params string $packageId
     *
     * @return array<int, mixed>
     */
    public function getSettingActionsByPackageId(string $packageId): array
    {
        return $this->settingRepository->getSettingActionsByPackageId($packageId);
    }

    /**
     * @inheritDoc
     */
    public function installCustomPointSettings(array $default = []): void
    {
        $customSettings = PackageManager::discoverSettings('getActivityPointSettings');

        $allRoles = resolve(RoleRepositoryInterface::class)
            ->getUsableRoles()
            ->pluck('id')
            ->toArray();

        foreach ($allRoles as $role) {
            foreach ($customSettings as $config) {
                foreach ($config as $settings) {
                    foreach ($settings as $setting) {
                        $setting['role_id'] = $role;
                        $description        = sprintf(
                            'activitypoint::phrase.%s_description',
                            str_replace('.', '_', $setting['name'])
                        );
                        $setting            = Arr::add($setting, 'description_phrase', $description);
                        $params             = array_merge($default, $setting);

                        PointSetting::query()->firstOrCreate([
                            'role_id' => $role,
                            'name'    => $params['name'],
                        ], $params);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isCustomInstalled(string $packageId): bool
    {
        $customSettings = PackageManager::discoverSettings('getActivityPointSettings');
        $result         = false;

        foreach ($customSettings as $config) {
            if (array_key_exists($packageId, $config)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param User $context
     * @param User $owner
     * @param int  $points
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function giftPoints(User $context, User $owner, int $points): bool
    {
        policy_authorize(StatisticPolicy::class, 'giftPoint', $context, $owner);

        //context send
        $sentActionParams = [
            'user'      => $owner instanceof UserModel ? $owner->display_name : '',
            'url'       => $owner instanceof UserModel ? $owner->toLink() : '',
            'points'    => $points,
            'is_self'   => (int) ($context->entityId() == $owner->entityId()),
            'user_name' => $owner instanceof UserModel ? $owner->display_name : '',
        ];

        $sentExtra = [
            'action'           => 'activitypoint::phrase.context_gifted_points_for_users',
            'action_type_name' => ActionType::ACTIVITYPOINT_GIFTING_POINTS_TYPE,
            'action_params'    => $sentActionParams,
        ];

        $this->addPoints($context, $context, 0 - $points, self::TYPE_SENT, $sentExtra);

        //owner received
        $receivedActionParams = [
            'user'      => $context instanceof UserModel ? $context->display_name : '',
            'url'       => $context instanceof UserModel ? $context->toLink() : '',
            'points'    => $points,
            'is_self'   => (int) ($context->entityId() == $owner->entityId()),
            'user_name' => $owner instanceof UserModel ? $owner->display_name : '',
        ];

        $receivedExtra = [
            'action'           => 'activitypoint::phrase.context_were_gifted_points_from_users',
            'action_type_name' => ActionType::ACTIVITYPOINT_GIFTED_POINTS_TYPE,
            'action_params'    => $receivedActionParams,
        ];

        $this->addPoints($owner, $owner, $points, self::TYPE_RECEIVED, $receivedExtra);

        $handler = new ReceivedGiftedPointsNotification($context);
        $handler->setData(['points' => $points]);

        // Sending notification to owner to inform that he/she is gifted points from context
        Notification::send($owner, $handler);

        return true;
    }

    public function getMinPointByIds(array $userIds): int
    {
        return $this->statisticRepository->getMinPointByIds($userIds);
    }

    public function isSubtracted(int $type): bool
    {
        return in_array($type, [self::TYPE_SENT, self::TYPE_SPENT, self::TYPE_RETRIEVED]);
    }

    public function isAdded(int $type): bool
    {
        return !$this->isSubtracted($type);
    }

    public function decreaseUserPointsWithAction(User $user, int $points, string $action, string $actionTypeName, array $actionParams = []): bool
    {
        $this->addPoints($user, $user, -1 * $points, self::TYPE_SPENT, [
            'action'           => $action,
            'action_type_name' => $actionTypeName,
            'action_params'    => $actionParams,
            'module_id'        => 'activitypoint',
        ]);

        return true;
    }

    public function isPackageAvailableForPurchase(User $user, PointPackage $package): bool
    {
        $prices       = $package->price;
        $userCurrency = app('currency')->getUserCurrencyId($user);

        if (!is_array($prices)) {
            return false;
        }

        $price = Arr::get($prices, $userCurrency);

        if (!is_numeric($price)) {
            return false;
        }

        if ($price < 0) {
            return false;
        }

        return true;
    }

    public function getFormValues(User $context, PointPackage $package): array
    {
        $currencyId = app('currency')->getUserCurrencyId($context);

        $price = Arr::get($package->price, $currencyId, 0);

        return [
            'price'       => $price,
            'currency_id' => $currencyId,
        ];
    }

    public function generateTransactionId(Order $order): string
    {
        return sprintf('apt_%s', md5(uniqid() . $order->entityId() . $order->entityType()));
    }

    public function getNextPaymentForm($context, array $params): ?AbstractForm
    {
        $id     = Arr::get($params, 'id');
        $action = Arr::get($params, 'action', apiUrl('activitypoint.package.purchase', ['id' => $id]));
        $method = Arr::get($params, 'method', FormConstants::METHOD_POST);

        /**
         * @var PointPackage $package
         */
        $package = resolve(PointPackageRepositoryInterface::class)->find($id);

        $values = self::getFormValues($context, $package);

        return Payment::getNextPaymentForm($context, Arr::get($params, 'payment_gateway'), $params, [
            'price'             => Arr::get($values, 'price'),
            'currency_id'       => Arr::get($values, 'currency_id'),
            'action_url'        => $action,
            'method'            => $method,
            'order_detail_info' => [
                [
                    'title'       => __p('activitypoint::phrase.point_package_title', ['title' => $package->toTitle()]),
                    'link'        => '/activitypoint/packages',
                    'quantity'    => 1,
                    'price'       => Arr::get($values, 'price'),
                    'currency'    => Arr::get($values, 'currency_id'),
                    'sub_total'   => 0,
                    'entity_type' => $package->entityType(),
                    'entity_id'   => $package->entityId(),
                ],
            ],
        ]);
    }
}
