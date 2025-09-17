<?php

namespace MetaFox\Page\Support;

use Illuminate\Support\Carbon;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsPendingInvite
{
    public function after($context, Reducer $reducer)
    {
        $userId = $context->id;
        $pages  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Page)
            ->map(fn ($x) => $x->id);

        if ($pages->isEmpty()) {
            return;
        }

        $key = fn ($id) => sprintf('user::isPendingInvited(user:%s,owner:%s)', $userId, $id);

        $data = $pages->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return PageInvite::query()
            ->where('owner_id', $userId)
            ->where('status_id', PageInvite::STATUS_PENDING)
            ->where('invite_type', PageInvite::INVITE_MEMBER)
            ->whereDate('expired_at', '>=', Carbon::now()->toDateTimeString())
            ->whereIn('page_id', $pages->all())
            ->get(['page_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->page_id)] = true;

                return $carry;
            }, $data);
    }
}
