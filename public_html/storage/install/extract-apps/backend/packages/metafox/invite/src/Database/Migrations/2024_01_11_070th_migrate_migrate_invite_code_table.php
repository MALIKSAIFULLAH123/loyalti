<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (!Schema::hasTable('invites')) {
            return;
        }

        \MetaFox\Invite\Models\Invite::query()
            ->where('status_id', \MetaFox\Invite\Models\Invite::INVITE_PENDING)
            ->whereNotNull('expired_at')
            ->each(function (\MetaFox\Invite\Models\Invite $invite) {
                if (null === $invite->user) {
                    return;
                }

                /**
                 * @var \MetaFox\Invite\Models\InviteCode $inviteCode
                 */
                $inviteCode = resolve(\MetaFox\Invite\Repositories\InviteCodeRepositoryInterface::class)->createCode($invite->user, \Illuminate\Support\Carbon::parse($invite->expired_at));

                $invite->update([
                    'invite_code' => $inviteCode->code,
                ]);
            });
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
