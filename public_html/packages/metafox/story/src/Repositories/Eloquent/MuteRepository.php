<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Story\Models\Mute;
use MetaFox\Story\Repositories\MuteRepositoryInterface;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class MutedRepository
 * @method Mute find($id, $columns = ['*'])
 * @method Mute getModel()
 */
class MuteRepository extends AbstractRepository implements MuteRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return Mute::class;
    }

    public function mute(User $context, array $attributes): Mute
    {
        $ownerId = Arr::get($attributes, 'user_id');
        $owner   = UserEntity::getById($ownerId)?->detail;

        if (!$owner instanceof User) {
            abort(403);
        }

        Arr::set($attributes, 'user_id', $context->entityId());
        Arr::set($attributes, 'user_type', $context->entityType());
        Arr::set($attributes, 'owner_id', $owner->entityId());
        Arr::set($attributes, 'owner_type', $owner->entityType());
        Arr::set($attributes, 'expired_at', StoryFacades::parseMuteExpiredAt(Arr::pull($attributes, 'time')));

        $model = $this->getModel()->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    /**
     * @inheritDoc
     */
    public function viewMuted(User $context, array $attributes): Builder
    {
        $query = $this->getModel()->newQuery();

        return $query->where('user_id', $context->entityId());
    }

    /**
     * @inheritDoc
     */
    public function unmute(User $context, array $attributes): bool
    {
        $ownerId = Arr::get($attributes, 'user_id');

        $this->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->where('owner_id', $ownerId)
            ->delete();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMuted(User $context, int $id): bool
    {
        return $this->find($id)->delete();
    }

    /**
     * @inheritDoc
     */
    public function isMuted(User $context, int $ownerId): bool
    {
        $contextId = $context->entityId();

        return LoadReduce::get(
            sprintf('story_mute::exists(user:%s,owner:%s)', $contextId, $ownerId),
            fn() => $this->getModel()->newQuery()
                ->where('owner_id', $ownerId)
                ->where('user_id', $contextId)
                ->exists()
        );
    }

    public function getUserMutedBuilder(User $context, array $attributes): Builder
    {
        $contextId     = $context->entityId();
        $relatedUserId = Arr::get($attributes, 'related_user_id', 0);
        $query         = $this->getModel()->newQuery();

        if ($relatedUserId > 0) {
            $query->whereNot('story_muted.owner_id', $relatedUserId);
        }

        return $query->select('story_muted.owner_id')
            ->where('story_muted.user_id', $contextId);
    }
}
