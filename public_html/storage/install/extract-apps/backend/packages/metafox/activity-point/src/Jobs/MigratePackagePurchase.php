<?php


namespace MetaFox\ActivityPoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Jobs\AbstractJob;

class MigratePackagePurchase extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $orders = Order::query()->where([
            'status'    => Order::STATUS_COMPLETED,
            'item_type' => PackagePurchase::ENTITY_TYPE,
        ])->cursor();

        if (!$orders->count()) {
            return;
        }

        /**@var Order[] $orders */
        foreach ($orders as $order) {
            $transaction = $order?->transactions()?->first();
            $order->item->update(['transaction_id' => $transaction?->gateway_transaction_id]);
        }
    }
}
