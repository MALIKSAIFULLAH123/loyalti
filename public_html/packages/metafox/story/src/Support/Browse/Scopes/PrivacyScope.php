<?php

namespace MetaFox\Story\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope as BasePrivacyScope;

class PrivacyScope extends BasePrivacyScope
{
    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table         = $model->getTable();
        $primaryKey    = sprintf('%s.%s', $table, $model->getKeyName());
        $streamTable   = $model->privacyStreams()->getRelated()->getTable();
        $streamTableAs = sprintf('%s AS stream', $streamTable);

        $builder->whereIn($primaryKey, function ($query) use ($streamTableAs) {
            $query->select('stream.item_id')->from($streamTableAs)
                ->join('core_privacy_members AS member', function (JoinClause $join) {
                    $join->on('stream.privacy_id', '=', 'member.privacy_id')
                        ->where('member.user_id', '=', $this->getUserId());
                });
        });
    }

}
