<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Video\Policies\VideoPolicy;

class QueryAlbumItemsListener
{
    public function __construct()
    {
    }

    /**
     * @param User    $context
     * @param Content $content
     * @param mixed   $query
     *
     * @return void
     */
    public function handle(User $context, Content $content, EloquentBuilder $query): void
    {
        $query->orWhereIn('photo_album_item.item_id', function (Builder $query) use ($context, $content) {
            $query->select('videos.id')->from('videos')
                ->where('videos.album_id', '=', $content->entityId());

            if (!$context->hasPermissionTo('video.moderate')) {
                // Privacy check
                $query->join('video_privacy_streams as stream', function (JoinClause $joinClause) use ($context) {
                    $joinClause->on('stream.item_id', '=', 'videos.id');
                });
                $query->join('core_privacy_members as member', function (JoinClause $join) use ($context) {
                    $join->on('stream.privacy_id', '=', 'member.privacy_id')
                        ->where('member.user_id', '=', $context->entityId());
                });
            }

            if (!policy_check(VideoPolicy::class, 'approveByOwner', $context, $content->owner)) {
                $query->where(function (Builder $builder) use ($context) {
                    $builder->where('videos.user_id', $context->entityId())
                        ->orWhere('videos.is_approved', MetaFoxConstant::IS_ACTIVE);
                });
            }
        });
    }
}
