<?php

namespace MetaFox\Mfa\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Platform\Jobs\AbstractJob;

class DeleteInactiveUserVerifyCodeJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return 'metafox_mfa_' . __CLASS__;
    }

    public function handle(): void
    {
        UserVerifyCode::query()->where(['is_active' => 0])->delete();
    }
}
