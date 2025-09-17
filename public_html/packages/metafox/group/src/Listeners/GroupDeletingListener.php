<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Group\Models\Group;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GroupDeletingListener
{
    /**
     * @param mixed $group
     *
     * @return void
     */
    public function handle(mixed $group): void
    {
        if (!$group instanceof Group) {
            return;
        }

        if (empty($group->profile_name)) {
            return;
        }

        $group->update([
            'profile_name' => md5(Str::random(32) . Carbon::now()->timestamp),
        ]);
    }
}
