<?php

namespace MetaFox\BackgroundStatus\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\BackgroundStatus\Repositories\BgsBackgroundRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Support\Browse\Scopes\NotDeleteScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class BgsCollectionRepository.
 *
 * @method BgsCollection getModel()
 * @method BgsCollection find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class BgsCollectionRepository extends AbstractRepository implements BgsCollectionRepositoryInterface
{
    public function model(): string
    {
        return BgsCollection::class;
    }

    protected function bgRepository(): BgsBackgroundRepositoryInterface
    {
        return resolve(BgsBackgroundRepositoryInterface::class);
    }

    public function viewBgsCollectionsForAdmin(User $context, array $attributes): Paginator
    {
        policy_authorize(BgsCollectionPolicy::class, 'viewAny', $context);

        return $this->getModel()->newQuery()
            ->whereNot('is_deleted', BgsCollection::IS_DELETED)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->paginate($attributes['limit'] ?? 12);
    }

    public function viewBgsCollectionsForFE(User $context, array $attributes): Paginator
    {
        policy_authorize(BgsCollectionPolicy::class, 'viewAny', $context);

        return $this->getModel()->newModelInstance()
            ->with([
                'backgrounds' => function (HasMany $query) {
                    $query->where('is_deleted', 0);
                    $query->orderBy('ordering')->orderBy('id');
                },
            ])
            ->where('total_background', '>', 0)
            ->where('is_deleted', 0)
            ->where('is_active', BgsCollection::IS_ACTIVE)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->simplePaginate($attributes['limit']);
    }

    public function getBackgrounds(User $context, array $attributes): Paginator
    {
        policy_authorize(BgsCollectionPolicy::class, 'viewAny', $context);

        $bgsCollection = $this->find($attributes['collection_id']);
        $this->checkIsDeleted($bgsCollection);

        $notDeleteScope = new NotDeleteScope();

        return $bgsCollection->backgrounds()
            ->addScope($notDeleteScope)
            ->simplePaginate($attributes['limit']);
    }

    public function viewBgsCollection(User $context, int $id): BgsCollection
    {
        policy_authorize(BgsCollectionPolicy::class, 'viewAny', $context);
        $bgsCollection = $this->find($id);

        $this->checkIsDeleted($bgsCollection);

        $bgsCollection->load([
            'backgrounds' => function (HasMany $query) {
                $notDeleteScope = new NotDeleteScope();

                return $query->addScope($notDeleteScope);
            },
        ]);

        return $bgsCollection;
    }

    public function createBgsCollection(User $context, array $attributes): BgsCollection
    {
        policy_authorize(BgsCollectionPolicy::class, 'create', $context);

        $backgroundTempFile = Arr::get($attributes, 'background_temp_file', []);
        unset($attributes['background_temp_file']);

        /** @var BgsCollection $bgsCollection */
        $bgsCollection = parent::create($attributes);

        $bgsCollection->refresh();
        $this->bgRepository()->uploadBackgrounds($context, $bgsCollection, $backgroundTempFile);

        return $bgsCollection;
    }

    public function updateBgsCollection(User $context, int $id, array $attributes): BgsCollection
    {
        policy_authorize(BgsCollectionPolicy::class, 'update', $context);
        $bgsCollection = $this->find($id);

        $backgroundTempFile = Arr::get($attributes, 'background_temp_file', []);
        unset($attributes['background_temp_file']);

        $this->bgRepository()->uploadBackgrounds($context, $bgsCollection, $backgroundTempFile);

        $bgsCollection->fill($attributes);
        $bgsCollection->save();

        return $bgsCollection->refresh();
    }

    public function updateMainBackground(BgsCollection $bgsCollection, int $mainBackgroundId = 0): void
    {
        if ($mainBackgroundId == 0) {
            $notDeleteScope = new NotDeleteScope();

            /** @var BgsBackground $background */
            $background = $bgsCollection->backgrounds()
                ->addScope($notDeleteScope)
                ->first();

            if (null != $background) {
                $mainBackgroundId = $background->entityId();
            }
        }

        $bgsCollection->update(['main_background_id' => $mainBackgroundId]);
    }

    public function deleteBgsCollection(User $context, int $id): bool
    {
        policy_authorize(BgsCollectionPolicy::class, 'delete', $context);

        $bgsCollection = $this->find($id);

        return $bgsCollection->update(['is_deleted' => BgsCollection::IS_DELETED]);
    }

    public function deleteBackground(User $context, int $id): bool
    {
        policy_authorize(BgsCollectionPolicy::class, 'update', $context);

        /** @var BgsBackground $bgsBackground */
        $bgsBackground = BgsBackground::query()->findOrFail($id);

        return $bgsBackground->update(['is_deleted' => BgsBackground::IS_DELETED]);
    }

    /**
     * @param ?BgsCollection $bgsCollection
     * @param string         $action
     *
     * @throws ValidationException
     */
    private function checkCanUpdate(?BgsCollection $bgsCollection, string $action = 'update'): void
    {
        if (!$bgsCollection) {
            return;
        }

        $this->checkIsDeleted($bgsCollection);

        if ($bgsCollection->view_only) {
            throw ValidationException::withMessages([
                __p('backgroundstatus::validation.cant_action_default_background_collection', ['action' => $action]),
            ]);
        }

        if ($action == 'delete') {
            if ($bgsCollection->is_default) {
                throw ValidationException::withMessages([
                    __p(
                        'backgroundstatus::validation.cant_action_default_background_collection',
                        ['action' => $action]
                    ),
                ]);
            }
        }
    }

    /**
     * @param BgsCollection $bgsCollection
     *
     * @throws ValidationException
     */
    private function checkIsDeleted(BgsCollection $bgsCollection): void
    {
        if ($bgsCollection->is_deleted) {
            throw ValidationException::withMessages([
                __p('backgroundstatus::validation.background_collection_already_deleted'),
            ]);
        }
    }

    public function getTotalCollectionActive(): int
    {
        return $this->getModel()->newQuery()
            ->where('is_deleted', 0)
            ->where('is_active', BgsCollection::IS_ACTIVE)->count();
    }
}
