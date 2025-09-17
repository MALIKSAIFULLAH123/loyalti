<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Story\Models\BackgroundSet;
use MetaFox\Story\Models\StoryBackground;
use MetaFox\Story\Repositories\StoryBackgroundRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StoryBackgroundRepository.
 * @method StoryBackground find($id, $columns = ['*'])
 * @method StoryBackground getModel()
 */
class StoryBackgroundRepository extends AbstractRepository implements StoryBackgroundRepositoryInterface
{
    public function model()
    {
        return StoryBackground::class;
    }

    public function uploadBackgrounds(User $context, BackgroundSet $backgroundSet, array $attributes): void
    {
        $newBackgrounds = array_filter($attributes, function ($item) {
            return $item['status'] == MetaFoxConstant::FILE_NEW_STATUS;
        });

        $removedBackgrounds = array_filter($attributes, function ($item) {
            return $item['status'] == MetaFoxConstant::FILE_REMOVE_STATUS;
        });

        $updatedBackgrounds = array_filter($attributes, function ($item) {
            return $item['status'] == MetaFoxConstant::FILE_UPDATE_STATUS;
        });

        $this->createBackgrounds($backgroundSet->entityId(), $newBackgrounds);

        $this->removeBackgrounds($removedBackgrounds);

        $this->updateBackgrounds($updatedBackgrounds);
    }

    protected function createBackgrounds(int $setId, array $newBackgrounds): void
    {
        if (empty($newBackgrounds)) {
            return;
        }

        foreach ($newBackgrounds as $newBackground) {
            $tempFileId = Arr::get($newBackground, 'temp_file');

            if (!$tempFileId) {
                continue;
            }

            $tempFile = upload()->getFile($tempFileId);
            $model    = new StoryBackground();

            $model->fill([
                'set_id'        => $setId,
                'image_file_id' => $tempFile->entityId(),
                'ordering'      => $newBackground['ordering'],
            ]);

            $model->save();

            upload()->rollUp($tempFileId);
        }
    }

    protected function removeBackgrounds(array $removedBackgrounds): void
    {
        $backgroundIds = Arr::pluck($removedBackgrounds, 'id');

        if (empty($backgroundIds)) {
            return;
        }

        $backgrounds = $this->getModel()->newModelQuery()
            ->whereIn('id', $backgroundIds)
            ->get();

        if (0 === $backgrounds->count()) {
            return;
        }

        foreach ($backgrounds as $background) {
            $background->update(['is_deleted' => 1]);
        }
    }

    protected function updateBackgrounds(array $updatedBackgrounds): void
    {
        foreach ($updatedBackgrounds as $params) {
            $background = $this->find($params['id']);

            $background?->update(['ordering' => $params['ordering']]);
        }
    }
}
