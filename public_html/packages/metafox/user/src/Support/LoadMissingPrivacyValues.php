<?php

namespace MetaFox\User\Support;

use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\User\Models\UserPrivacy;

class LoadMissingPrivacyValues
{
    /**
     * @param  User       $context
     * @param  Reducer    $reducer
     * @return mixed|null
     */
    public function after(User $context, Reducer $reducer)
    {
        $userId = $context->id;

        $users = $reducer->entities()
            ->filter(fn ($x) => $x instanceof User && $x->entityId() !== $userId)
            ->map(fn ($x) => $x->id)
            ->unique();

        if ($users->isEmpty()) {
            return null;
        }

        $key         = fn ($userId) => sprintf('privacy::all(user:%s)', $userId);
        $isMemberKey = fn ($privacyId) => sprintf('privacy::isMember(user:%s,privacy:%s)', $userId, $privacyId);

        $data = $users->reduce(function ($carry, $userId) use ($key) {
            $carry[$key($userId)] = [];

            return $carry;
        }, []);

        $privacyIds = Privacy::query()
            ->whereIn('user_id', $users)
            ->where('privacy', '<', 10)
            ->limit(200)
            ->pluck('privacy_id')
            ->toArray();

        $data = UserPrivacy::query()
            ->whereIn('user_id', $users)
            ->limit(100) // limit because of admincp.
            ->get()
            ->reduce(function ($carry, $item) use ($key, &$privacyIds) {
                $carry[$key($item->user_id)][$item->name] = $item->toArray();

                array_push($privacyIds, $item->privacy_id);

                return $carry;
            }, $data);

        if (empty($privacyIds)) {
            return $data;
        }

        // build privacy members
        $data = array_reduce($privacyIds, function ($carry, $id) use ($isMemberKey) {
            $carry[$isMemberKey($id)] = false;

            return $carry;
        }, $data);

        return PrivacyMember::query()
            ->where(['user_id' => $userId])
            ->selectRaw('privacy_id')
            ->whereIn('privacy_id', $privacyIds)
            ->get()
            ->reduce(function ($carry, $item) use ($isMemberKey) {
                $carry[$isMemberKey($item->privacy_id)] = true;

                return $carry;
            }, $data);
    }
}
