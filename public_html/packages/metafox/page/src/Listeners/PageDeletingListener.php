<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Page\Models\Page;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PageDeletingListener
{
    /**
     * @param mixed $page
     *
     * @return void
     */
    public function handle(mixed $page): void
    {
        if (!$page instanceof Page) {
            return;
        }

        if (empty($page->profile_name)) {
            return;
        }

        $page->update([
            'profile_name' => md5(Str::random(32) . Carbon::now()->timestamp),
        ]);
    }
}
