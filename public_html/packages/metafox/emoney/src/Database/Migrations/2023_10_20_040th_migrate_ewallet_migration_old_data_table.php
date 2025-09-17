<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->removeOldPhrases();
        $this->migrateNotificationTypes();
        $this->migratePaymentType();
        $this->removeOldEntityDriver();
        $this->removeWebMenu();
    }

    private function removeWebMenu(): void
    {
        \MetaFox\Menu\Models\Menu::query()
            ->where([
                'name' => 'ewallet.emoney_withdraw_request.itemActionMenu',
                'package_id' => 'metafox/emoney',
                'resource_name' => 'emoney_withdraw_request',
                'resolution' => 'web',
            ])
            ->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'ewallet.emoney_withdraw_request.itemActionMenu',
                'package_id' => 'metafox/emoney',
                'resolution' => 'web',
            ])
            ->delete();
    }
    private function removeOldPhrases(): void
    {
        \MetaFox\Localize\Models\Phrase::query()
            ->where(['namespace' => 'emoney'])
            ->delete();
    }

    private function migrateNotificationTypes(): void
    {
        \MetaFox\Notification\Models\NotificationModule::query()
            ->where('module_id', 'emoney')
            ->get()
            ->each(function ($module) {
                \MetaFox\Notification\Models\ModuleSetting::query()
                    ->where('module_id', $module->entityId())
                    ->delete();

                $module->delete();
            });

        \MetaFox\Notification\Models\Type::query()
            ->where(['module_id' => 'ewallet'])
            ->get()
            ->each(function ($type) {
                $old = $type->type;

                $new = str_replace('emoney', 'ewallet', $old);

                $type->update(['type' => $new]);

                \MetaFox\Notification\Models\Notification::query()
                    ->where(['type' => $old])
                    ->update(['type' => $new]);
            });

        foreach (['emoney_transaction', 'emoney_withdraw_request'] as $itemType) {
            $new = str_replace('emoney', 'ewallet', $itemType);

            \MetaFox\Notification\Models\Notification::query()
                ->where(['item_type' => $itemType])
                ->update(['item_type' => $new]);
        }
    }

    private function migratePaymentType(): void
    {
        \MetaFox\Payment\Models\Order::query()
            ->where(['item_type' => 'emoney_withdraw_request'])
            ->update(['item_type' => \MetaFox\EMoney\Models\WithdrawRequest::ENTITY_TYPE]);
    }

    private function removeOldEntityDriver(): void
    {
        \MetaFox\Core\Models\Driver::query()
            ->where([
                'type' => 'entity',
                'module_id' => 'ewallet'
            ])
            ->where('name', database_driver() == 'pgsql' ? 'ilike' : 'like', 'emoney_%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
