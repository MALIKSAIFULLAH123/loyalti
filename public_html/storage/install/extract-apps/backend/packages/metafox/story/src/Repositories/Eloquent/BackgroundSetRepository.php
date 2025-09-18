<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Story\Models\BackgroundSet;
use MetaFox\Story\Models\StoryBackground;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;
use MetaFox\Story\Repositories\StoryBackgroundRepositoryInterface;
use MetaFox\Story\Support\Browse\Scopes\NotDeleteScope;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BackgroundSetAdminRepository.
 *
 * @method BackgroundSet find($id, $columns = ['*'])
 * @method BackgroundSet getModel()
 */
class BackgroundSetRepository extends AbstractRepository implements BackgroundSetRepositoryInterface
{
    public function model()
    {
        return BackgroundSet::class;
    }

    protected function bgRepository(): StoryBackgroundRepositoryInterface
    {
        return resolve(StoryBackgroundRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function viewBackgroundSets(User $context, array $attributes): Paginator
    {
        return $this->getModel()->newModelInstance()
            ->with([
                'backgrounds' => function (HasMany $query) {
                    $query->where('is_deleted', 0);
                    $query->orderBy('ordering')->orderBy('id');
                },
            ])
            ->where('total_background', '>', 0)
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->simplePaginate($attributes['limit']);
    }

    public function viewBackgroundSetForFE(User $context, array $attributes): Paginator
    {
        return $this->getModel()->newModelInstance()
            ->with([
                'backgrounds' => function (HasMany $query) {
                    $query->where('is_deleted', 0);
                    $query->orderBy('ordering')->orderBy('id');
                },
            ])
            ->where('total_background', '>', 0)
            ->where('is_deleted', 0)
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->orderBy('id')
            ->simplePaginate($attributes['limit']);
    }

    /**
     * @param User $context
     * @param int  $id
     * @return BackgroundSet
     * @throws ValidationException
     */
    public function viewBackgroundSet(User $context, int $id): BackgroundSet
    {
        $backgroundSet = $this->find($id);

        $this->checkIsDeleted($backgroundSet);

        $backgroundSet->load([
            'backgrounds' => function (HasMany $query) {
                $notDeleteScope = new NotDeleteScope();

                return $query->addScope($notDeleteScope);
            },
        ]);

        return $backgroundSet;
    }

    /**
     * @param User  $context
     * @param array $attributes
     * @return BackgroundSet
     * @throws ValidatorException
     */
    public function createBackgroundSet(User $context, array $attributes): BackgroundSet
    {
        $backgroundTempFile = Arr::get($attributes, 'background_temp_file', []);
        unset($attributes['background_temp_file']);

        /** @var BackgroundSet $backgroundSet */
        $backgroundSet = parent::create($attributes);

        $backgroundSet->refresh();
        $this->bgRepository()->uploadBackgrounds($context, $backgroundSet, $backgroundTempFile);

        return $backgroundSet;
    }

    /**
     * @param BackgroundSet $backgroundSet
     * @param int           $mainBackgroundId
     * @return void
     */
    public function updateMainBackground(BackgroundSet $backgroundSet, int $mainBackgroundId = 0): void
    {
        if ($mainBackgroundId == 0) {
            $notDeleteScope = new NotDeleteScope();

            /** @var StoryBackground $background */
            $background = $backgroundSet->backgrounds()
                ->addScope($notDeleteScope)
                ->first();

            if (null != $background) {
                $mainBackgroundId = $background->entityId();
            }
        }

        $backgroundSet->update(['main_background_id' => $mainBackgroundId]);
    }

    public function updateBackgroundSet(User $context, int $id, array $attributes): BackgroundSet
    {
        $backgroundSet = $this->find($id);

        $backgroundTempFile = Arr::get($attributes, 'background_temp_file', []);
        unset($attributes['background_temp_file']);

        $this->bgRepository()->uploadBackgrounds($context, $backgroundSet, $backgroundTempFile);

        $background = $this->bgRepository()->getModel()->newQuery()
            ->whereNot('is_deleted', MetaFoxConstant::IS_ACTIVE)
            ->where('set_id', $id)->orderBy('ordering')->first();

        if ($background instanceof StoryBackground) {
            Arr::set($attributes, 'main_background_id', $background->entityId());
        }

        $backgroundSet->update($attributes);

        return $backgroundSet->refresh();
    }

    public function deleteBackgroundSet(User $context, int $id): bool
    {
        $bgsCollection = $this->find($id);

        if ($bgsCollection->is_active) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('story::phrase.the_activated_collection_cannot_be_deleted'),
            ]));
        }

        return $bgsCollection->update(['is_deleted' => 1]);
    }

    /**
     * @param BackgroundSet $backgroundSet
     *
     * @throws ValidationException
     */
    private function checkIsDeleted(BackgroundSet $backgroundSet): void
    {
        if ($backgroundSet->is_deleted) {
            throw ValidationException::withMessages([
                __p('story::validation.background_collection_already_deleted'),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getBackgroundSetActive(): BackgroundSet
    {
        return $this->getModel()->firstWhere('is_active', MetaFoxConstant::IS_ACTIVE);
    }
}
