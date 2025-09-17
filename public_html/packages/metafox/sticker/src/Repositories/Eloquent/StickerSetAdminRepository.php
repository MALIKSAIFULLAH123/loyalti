<?php

namespace MetaFox\Sticker\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use MetaFox\Sticker\Repositories\StickerRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerSetAdminRepositoryInterface;
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
class StickerSetAdminRepository extends AbstractRepository implements StickerSetAdminRepositoryInterface
{
    public function model(): string
    {
        return StickerSet::class;
    }

    protected function stickerRepository(): StickerRepositoryInterface
    {
        return resolve(StickerRepositoryInterface::class);
    }

    public function viewStickerSets(User $context, array $attributes): Paginator
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);

        $notDeleteScope = new NotDeleteScope();

        $query = $this->getModel()->newQuery()
            ->orderByDesc('view_only')
            ->orderBy('title');

        return $query->addScope($notDeleteScope)
            ->simplePaginate($attributes['limit']);
    }

    public function createStickerSet(User $context, array $attributes): StickerSet
    {
        policy_authorize(StickerSetPolicy::class, 'create', $context);

        if (isset($attributes['sticker_temp_file'])) {
            foreach ($attributes['sticker_temp_file'] as $tempFileId) {
                $tempFile = upload()->getFile($tempFileId);
                $sticker  = [
                    'image_file_id' => $tempFile->id,
                ];

                if (isset($attributes['view_only'])) {
                    $sticker = array_merge($sticker, ['view_only' => $attributes['view_only']]);
                }

                $attributes['stickers'][] = $sticker;

                upload()->rollUp($tempFileId);
            }
        }

        /** @var StickerSet $stickerSet */
        $stickerSet = parent::create($attributes);

        return $stickerSet->refresh();
    }

    public function updateStickerSet(User $context, int $id, array $attributes): StickerSet
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);

        $stickerSet = $this->find($id);

        $this->checkCanUpdate($stickerSet);

        $file = Arr::get($attributes, 'file', []);
        unset($attributes['file']);

        $this->stickerRepository()->uploadStickers($context, $stickerSet, $file);

        $sticker = $this->stickerRepository()->getModel()->newQuery()
            ->where('set_id', $id)->orderBy('ordering')->first();

        if ($sticker instanceof Sticker) {
            Arr::set($attributes, 'thumbnail_id', $sticker->entityId());
        }

        $stickerSet->update($attributes);

        return $stickerSet->refresh();
    }

    public function viewStickerSet(User $context, int $id): StickerSet
    {
        policy_authorize(StickerSetPolicy::class, 'viewAny', $context);
        $stickerSet = $this->find($id);

        //        $this->checkIsDeleted($stickerSet);

        $stickerSet->load([
            'stickers' => function (HasMany $query) {
                $notDeleteScope = new NotDeleteScope();

                return $query->addScope($notDeleteScope);
            },
        ]);

        return $stickerSet;
    }

    public function deleteStickerSet(User $context, int $id): bool
    {
        policy_authorize(StickerSetPolicy::class, 'delete', $context);

        $stickerSet = $this->find($id);
        $this->checkCanUpdate($stickerSet, 'delete');

        return $stickerSet->update(['is_deleted' => StickerSet::IS_DELETED]);
    }

    public function toggleActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(StickerSetPolicy::class, 'update', $context);
        $stickerSet = $this->find($id);

        $this->checkIsDeleted($stickerSet);

        return $stickerSet->update(['is_active' => $isActive]);
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

    public function installStickerSet(User $context, array $attributes): StickerSet
    {
        policy_authorize(StickerSetPolicy::class, 'create', $context);

        $stickerSet = parent::create($attributes);
        $stickerSet->refresh();

        $file = Arr::get($attributes, 'file', []);
        unset($attributes['file']);

        $this->stickerRepository()->uploadStickers($context, $stickerSet, $file);

        return $stickerSet;
    }

    /**
     * @param StickerSet $stickerSet
     */
    private function checkIsDeleted(StickerSet $stickerSet): void
    {
        if ($stickerSet->is_deleted) {
            abort(422, __p('sticker::validation.sticker_set_already_deleted'));
        }
    }

    /**
     * @param StickerSet $stickerSet
     * @param string     $action
     */
    public function checkCanUpdate(StickerSet $stickerSet, string $action = 'update'): void
    {
        $this->checkIsDeleted($stickerSet);

        $errorMessage = __p('sticker::validation.cant_action_default_sticker_set', ['action' => $action]);

        if ($stickerSet->view_only) {
            abort(422, $errorMessage);
        }

        if ($action == 'delete') {
            if ($stickerSet->is_default) {
                abort(422, $errorMessage);
            }
        }
    }
}
