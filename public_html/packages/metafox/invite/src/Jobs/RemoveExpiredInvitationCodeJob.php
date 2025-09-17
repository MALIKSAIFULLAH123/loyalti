<?php

namespace MetaFox\Invite\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Platform\Jobs\AbstractJob;

class RemoveExpiredInvitationCodeJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        InviteCode::query()
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', Carbon::now())
            ->delete();
    }
}
