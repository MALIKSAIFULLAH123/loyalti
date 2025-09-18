<?php

namespace MetaFox\Sticker\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Sticker\Models\StickerRecent as Model;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use MetaFox\Sticker\Repositories\StickerRecentRepositoryInterface;

/**
 * Class StickerRepository.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 * @ignore
 * @codeCoverageIgnore
 */
class StickerRecentRepository extends AbstractRepository implements StickerRecentRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @param User $context
     * @param int  $stickerId
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function createRecentSticker(User $context, int $stickerId): Model
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);

        $recentSticker = $this->getModel()->newModelQuery()
            ->where('user_id', '=', $context->entityId())
            ->where('user_type', '=', $context->entityType())
            ->where('sticker_id', '=', $stickerId)
            ->first();

        if ($recentSticker instanceof Model) {
            $recentSticker->touch();
            $recentSticker->refresh();

            return $recentSticker;
        }

        $params = [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'sticker_id' => $stickerId,
        ];

        $newRecent = new Model();
        $newRecent->fill($params);
        $newRecent->save();

        // Delete older recent stickers defined on setting 'sticker.maximum_recent_sticker_can_create'
        $count = $this->getModel()
            ->where('user_id', $context->entityId())
            ->count();

        $offset = Settings::get('sticker.maximum_recent_sticker_can_create', 0);

        if ($count <= $offset) {
            return $newRecent;
        }

        $this->getModel()
            ->newModelQuery()
            ->where('user_id', $context->entityId())
            ->latest('updated_at')
            ->offset($offset)
            ->limit(max($count - $offset, 0))
            ->get()
            ->collect()
            ->each(function ($item, $key) {
                $item->delete();
            });

        return $newRecent;
    }
}
