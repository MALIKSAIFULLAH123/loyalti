<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Album;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope as Main;
use MetaFox\Platform\MetaFoxPrivacy;

class PrivacyScope extends Main
{
    protected function addPrivacyScope(Builder $builder, Model $model): void
    {
        $subQuery = DB::table('core_privacy_streams', 'stream')
            ->join('core_privacy_members AS member', function (JoinClause $join) {
                $join->on('stream.privacy_id', '=', 'member.privacy_id')
                    ->where('member.privacy_id', '<>', MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID)
                    ->where('member.user_id', '=', $this->getUserId());
            })
            ->select(['stream.item_id', 'stream.item_type'])
            ->distinct('stream.item_id', 'stream.item_type');

        $builder->leftJoinSub($subQuery, 'item', function (JoinClause $joinClause) {
            $joinClause->on('item.item_id', '=', 'photo_album_item.item_id');
            $joinClause->on('item.item_type', '=', 'photo_album_item.item_type');
        });
    }

    protected function addBlockedScope(Builder $builder, Model $model): void
    {
    }
}
