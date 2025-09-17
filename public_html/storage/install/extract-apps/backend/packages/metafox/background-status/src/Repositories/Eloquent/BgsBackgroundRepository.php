<?php

namespace MetaFox\BackgroundStatus\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Repositories\BgsBackgroundRepositoryInterface;
use MetaFox\BackgroundStatus\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class BgsBackgroundRepository.
 * @method BgsBackground getModel()
 * @method BgsBackground find($id, $columns = ['*'])
 *
 * @ignore
 * @codeCoverageIgnore
 */
class BgsBackgroundRepository extends AbstractRepository implements BgsBackgroundRepositoryInterface
{
    public function model(): string
    {
        return BgsBackground::class;
    }

    public function uploadBackgrounds(User $context, BgsCollection $bgsCollection, array $attributes): void
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

        $this->createBackgrounds($bgsCollection->entityId(), $newBackgrounds);

        $this->removeBackgrounds($removedBackgrounds);

        $this->updateBackgrounds($updatedBackgrounds);
    }

    protected function createBackgrounds(int $collectionId, array $newBackgrounds): void
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
            $model    = new BgsBackground();

            $model->fill([
                'collection_id' => $collectionId,
                'image_file_id' => $tempFile->entityId(),
                'text_color'    => Arr::get($newBackground, 'extra_info.text_color') ?: Support::WHITE_COLOR,
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

            $background?->update([
                'ordering'   => $params['ordering'],
                'text_color' => Arr::get($params, 'extra_info.text_color', $background->text_color),
            ]);
        }
    }
}
