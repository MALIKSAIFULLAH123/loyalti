<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

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
            $query->select('photos.id')->from('photos')
                ->where('photos.album_id', '=', $content->entityId());

            if (!$context->hasPermissionTo('photo.moderate')) {
                // Privacy check
                $query->join('photo_privacy_streams as stream', function (JoinClause $joinClause) use ($context) {
                    $joinClause->on('stream.item_id', '=', 'photos.id');
                });
                $query->join('core_privacy_members as member', function (JoinClause $join) use ($context) {
                    $join->on('stream.privacy_id', '=', 'member.privacy_id')
                        ->where('member.user_id', '=', $context->entityId());
                });
            }

            if (!policy_check(PhotoPolicy::class, 'approveByOwner', $context, $content->owner)) {
                $query->where(function (Builder $builder) use ($context) {
                    $builder->where('photos.user_id', $context->entityId())
                        ->orWhere('photos.is_approved', MetaFoxConstant::IS_ACTIVE);
                });
            }
        });
    }
}
