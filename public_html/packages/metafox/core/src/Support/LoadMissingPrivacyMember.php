<?php

namespace MetaFox\Core\Support;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingPrivacyMember
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return mixed|null
     */
    public function handle(User $context, Reducer $reducer)
    {
        $userId  = $context->id;
        $itemIds = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User && $x->id != $userId)
            ->map(fn ($x) => $x->id);

        if ($itemIds->isEmpty()) {
            return null;
        }

        $key = fn ($ownerId) => sprintf('privacy::hasAbilityOnOwner(user:%s,owner:%s)', $userId, $ownerId);

        // initial
        $data = $itemIds->reduce(function ($carry, $x) use ($userId, $key) {
            $carry[$key($x)] = [];

            return $carry;
        }, []);

        return DB::table('core_privacy_members as member')
            ->select('privacy.*')
            ->join('core_privacy as privacy', function (JoinClause $join) use ($userId) {
                $join->on('member.privacy_id', '=', 'privacy.privacy_id');
                $join->where('member.user_id', '=', $userId);
            })
            ->whereIn('item_id', $itemIds)
            ->get()
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->item_id)][] = [
                    'privacy_type' => $x->privacy_type,
                    'privacy'      => $x->privacy,
                ];

                return $carry;
            }, $data);
    }
}
