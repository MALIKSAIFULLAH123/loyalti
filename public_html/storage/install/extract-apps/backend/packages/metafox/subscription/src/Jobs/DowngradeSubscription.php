<?php

namespace MetaFox\Subscription\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Subscription\Notifications\ExpiredNotify;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

class DowngradeSubscription extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var SubscriptionPackageRepositoryInterface
     */
    protected $packageRepository;

    /**
     * @var SubscriptionInvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var Collection
     */
    protected $recurringPackages;

    /**
     * @param SubscriptionPackageRepositoryInterface $packageRepository
     * @param SubscriptionInvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(SubscriptionPackageRepositoryInterface $packageRepository, SubscriptionInvoiceRepositoryInterface $invoiceRepository)
    {
        parent::__construct();
        $this->packageRepository = $packageRepository;

        $this->invoiceRepository = $invoiceRepository;
    }

    public function handle()
    {
        $this->init();
        $this->downgradeExpirationByManualRenewMethod();
        $this->notifySubscriptionsByManualRenewMethod();
        $this->downgradeCanceledSubscriptionsByGateway();
    }

    protected function downgradeExpirationByManualRenewMethod(): void
    {
        if ($this->recurringPackages->count()) {
            foreach ($this->recurringPackages as $package) {
                $expiredInvoices = $this->invoiceRepository->getExpiredSubscriptions($package->entityId());

                if ($expiredInvoices->count()) {
                    foreach ($expiredInvoices as $expiredInvoice) {
                        $this->invoiceRepository->updatePayment($expiredInvoice->entityId(), Helper::getExpiredPaymentStatus());
                    }
                }
            }
        }
    }

    protected function notifySubscriptionsByManualRenewMethod(): void
    {
        if ($this->recurringPackages->count()) {
            foreach ($this->recurringPackages as $package) {
                if ($package->status == Helper::STATUS_DELETED) {
                    continue;
                }

                $notifiedInvoices = $this->invoiceRepository->getNotifiedInvoices($package->entityId());

                if ($notifiedInvoices->count()) {
                    $systemExpiredDays = (int) Settings::get('subscription.default_addon_expired_day', Helper::DEFAULT_EXPIRED_ADDON_DAY);

                    foreach ($notifiedInvoices as $notifiedInvoice) {
                        $notifyDate = Carbon::parse($notifiedInvoice->notified_at);

                        $expiredDate = Carbon::parse($notifiedInvoice->expired_at);

                        $isGreater = $expiredDate->timestamp > $notifyDate->timestamp;

                        switch ($isGreater) {
                            case true:
                                $days = $notifyDate->diffInDays($expiredDate) + $systemExpiredDays;
                                break;
                            default:
                                $days = $systemExpiredDays - $expiredDate->diffInDays($notifyDate);
                                break;
                        }

                        $notification = new ExpiredNotify($notifiedInvoice);

                        $notification->setDays($days);

                        $params = [$notifiedInvoice->user, $notification];

                        Notification::send(...$params);

                        if ($days > 1) {
                            $notifiedInvoice->update(['notified_at' => $notifyDate->addDay()]);
                        }
                    }
                }
            }
        }
    }

    protected function downgradeCanceledSubscriptionsByGateway(): void
    {
        if ($this->recurringPackages->count()) {
            foreach ($this->recurringPackages as $package) {
                $canceledSubscriptions = $this->invoiceRepository->getCanceledSubscriptionsByGateway($package->entityId());

                if ($canceledSubscriptions->count()) {
                    foreach ($canceledSubscriptions as $canceledSubscription) {
                        $this->invoiceRepository->updatePayment($canceledSubscription->entityId(), Helper::getExpiredPaymentStatus());
                    }
                }
            }
        }
    }

    protected function init(): void
    {
        $this->recurringPackages = $this->packageRepository->getRecurringPackages();
    }
}
