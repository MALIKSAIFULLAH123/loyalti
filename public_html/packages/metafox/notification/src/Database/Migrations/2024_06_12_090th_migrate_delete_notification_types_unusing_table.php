<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Notification\Models\NotificationSetting;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Support\TypeManager;

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
        $deleteTypes = [
            'new_password_requested', 'user_verify_email_signup', 'user_verify_phone_number',
        ];

        /** @var TypeManager $typeManager */
        $typeManager = resolve(TypeManager::class);
        $typeManager->handleDeletedTypeByName($deleteTypes);

        $this->revertNotificationSettingUser();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function revertNotificationSettingUser(): void
    {
        $queryType = Type::query()
            ->select('id')
            ->where('can_edit', 0);

        NotificationSetting::query()
            ->whereIn('type_id', $queryType)
            ->delete();
    }
};
