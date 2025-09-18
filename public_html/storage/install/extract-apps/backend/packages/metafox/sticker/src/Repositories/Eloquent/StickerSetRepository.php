<?php

namespace MetaFox\Sticker\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Models\StickerUserValue;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use MetaFox\Sticker\Support\Browse\Scopes\NotDeleteScope;

/**
 * Class StickerSetRepository.
 * @method StickerSet find($id, $columns = ['*'])
 * @method StickerSet getModel()
 *
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StickerSetRepository extends AbstractRepository implements StickerSetRepositoryInterface
{
    public function model(): string
    {
        return StickerSet::class;
    }

    public function viewStickerSetsAll(User $context, array $attributes): Paginator
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);

        $notDeleteScope = new NotDeleteScope();

        $query = $this->getModel()->newQuery()
            ->with([
                'thumbnail',
                'stickers' => function (HasMany $query) use ($notDeleteScope) {
                    return $query->addScope($notDeleteScope);
                },
            ])
            ->where('sticker_sets.total_sticker', '>', 0)
            ->where('sticker_sets.is_active', StickerSet::IS_ACTIVE)
            ->orderBy('sticker_sets.title');

        return $query
            ->addScope($notDeleteScope)
            ->simplePaginate($attributes['limit'], ['sticker_sets.*']);
    }

    public function viewStickerSetsUser(User $context, array $attributes): Paginator
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);

        $query = $this->getModel()->newModelQuery()
            ->with([
                'thumbnail',
                'stickers' => function (HasMany $query) {
                    return $query->addScope(new NotDeleteScope());
                },
            ])
            ->join('sticker_user_values as suv', function (JoinClause $join) use ($context) {
                $join->on('sticker_sets.id', '=', 'suv.set_id');
                $join->where('suv.user_id', $context->entityId());
                $join->where('sticker_sets.is_active', StickerSet::IS_ACTIVE);
                $join->where('sticker_sets.is_deleted', 0);
                $join->where('sticker_sets.total_sticker', '>', 0);
            })
            ->orderByDesc('suv.id');
        
        return $query->simplePaginate($attributes['limit'], ['sticker_sets.*']);
    }

    public function checkStickerSetAdded(int $userId, int $stickerSetId): bool
    {
        return StickerUserValue::query()
            ->where('user_id', $userId)
            ->where('set_id', $stickerSetId)
            ->exists();
    }

    public function viewStickerSet(User $context, int $id): StickerSet
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        $stickerSet->load([
            'stickers' => function (HasMany $query) {
                $notDeleteScope = new NotDeleteScope();

                return $query->addScope($notDeleteScope);
            },
        ]);

        return $stickerSet;
    }

    public function toggleActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        return $stickerSet->update(['is_active' => $isActive]);
    }

    public function deleteSticker(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);

        /** @var Sticker $sticker */
        $sticker = Sticker::query()->findOrFail($id);

        $this->checkCanUpdate($sticker->stickerSet);

        return $sticker->update(['is_deleted' => Sticker::IS_DELETED]);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addUserStickerSet(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'addUserStickerSet', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        $params = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'set_id'    => $id,
        ];

        $checkExist = StickerUserValue::query()
            ->where($params)
            ->exists();

        if ($checkExist) {
            throw ValidationException::withMessages([__p('sticker::validation.you_already_download_this_sticker_set')]);
        }

        $stickerUserSet = new StickerUserValue($params);

        return $stickerUserSet->save();
    }

    public function deleteUserStickerSet(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'addUserStickerSet', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        /** @var StickerUserValue $stickerUserSet */
        $stickerUserSet = StickerUserValue::query()->where([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'set_id'    => $id,
        ])->firstOrFail();

        return (bool) $stickerUserSet->delete();
    }

    public function getStickers(User $context, array $attributes): Paginator
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);

        $stickerSet = $this->find($attributes['sticker_set_id']);
        $this->checkIsDeleted($stickerSet);

        $notDeleteScope = new NotDeleteScope();

        return $stickerSet->stickers()
            ->addScope($notDeleteScope)
            ->simplePaginate($attributes['limit']);
    }

    public function markAsDefault(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        $countDefault = $this->getModel()->newQuery()
            ->where('is_default', StickerSet::IS_DEFAULT)
            ->count('id');

        if (StickerSet::MAX_DEFAULT == $countDefault) {
            throw ValidationException::withMessages([__p('sticker::validation.you_can_set_only_two_default_sticker_set')]);
        }

        return $stickerSet->update(['is_default' => StickerSet::IS_DEFAULT]);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function removeDefault(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        return $stickerSet->update(['is_default' => 0]);
    }

    public function updateThumbnail(StickerSet $stickerSet, int $thumbnailId = 0): void
    {
        if ($thumbnailId == 0) {
            $notDeleteScope = new NotDeleteScope();

            /** @var Sticker $sticker */
            $sticker = $stickerSet->stickers()
                ->addScope($notDeleteScope)
                ->first();

            if (null != $sticker) {
                $thumbnailId = $sticker->entityId();
            }
        }

        $stickerSet->update(['thumbnail_id' => $thumbnailId]);
    }

    public function orderingStickerSet(User $context, array $orders): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            StickerSet::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    public function orderingSticker(User $context, array $orders): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            Sticker::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    /**
     * @param StickerSet $stickerSet
     * @param string     $action
     *
     * @throws ValidationException
     */
    private function checkCanUpdate(StickerSet $stickerSet, string $action = 'update'): void
    {
        $this->checkIsDeleted($stickerSet);

        if ($stickerSet->view_only) {
            throw ValidationException::withMessages([
                __p('sticker::validation.cant_action_default_sticker_set', ['action' => $action]),
            ]);
        }

        if ($action == 'delete') {
            if ($stickerSet->is_default) {
                throw ValidationException::withMessages([
                    __p('sticker::validation.cant_action_default_sticker_set', ['action' => $action]),
                ]);
            }
        }
    }

    /**
     * @param StickerSet $stickerSet
     *
     * @throws ValidationException
     */
    public function checkIsDeleted(StickerSet $stickerSet): void
    {
        if ($stickerSet->is_deleted) {
            throw ValidationException::withMessages([
                __p('sticker::validation.sticker_set_already_deleted'),
            ]);
        }
    }

    /**
     * @param int $stickerId
     *
     * @return Sticker|null
     */
    public function getSticker(int $stickerId): ?Sticker
    {
        /** @var Sticker $sticker */
        $sticker = Sticker::query()->find($stickerId);

        return $sticker;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewStickerSets(User $context, array $attributes): Paginator
    {
        $view = Arr::get($attributes, 'view', 'all');

        return match ($view) {
            'my'    => $this->viewStickerSetsUser($context, $attributes),
            default => $this->viewStickerSetsAll($context, $attributes),
        };
    }

    /**
     * @inheritDoc
     */
    public function addDefaultStickerForUser(User $user): void
    {
        $params   = [];
        $stickers = $this->getModel()->newQuery()
            ->where('is_default', StickerSet::IS_DEFAULT)
            ->get();

        /** @var StickerSet $sticker */
        foreach ($stickers as $sticker) {
            $params = [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'set_id'    => $sticker->entityId(),
            ];
        }

        $stickerUserSet = new StickerUserValue();

        $stickerUserSet->newQuery()->insertOrIgnore($params);
    }
}
