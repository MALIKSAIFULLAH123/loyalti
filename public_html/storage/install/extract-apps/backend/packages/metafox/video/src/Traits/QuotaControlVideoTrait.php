<?php

namespace MetaFox\Video\Traits;

use Illuminate\Support\Arr;
use MetaFox\Video\Models\Video;
use MetaFox\Platform\Contracts\User;

trait QuotaControlVideoTrait
{

    protected function getTotalFileByType(array $params, string $type): int
    {
        $files = Arr::get($params, $type, []);

        $collectionFiles = collect($files)->groupBy('type')->map->count();

        return Arr::get($collectionFiles, Video::ENTITY_TYPE, 0) ?? 0;
    }

    protected function getTotalFileNew(array $params, ?string $newFileParams, ?string $removeFileParams): int
    {
        if (!$newFileParams && !$removeFileParams) {
            return 1;
        }

        $totalNewFiles = $newFileParams ? $this->getTotalFileByType($params, $newFileParams) : 0;

        $totalRemoveFiles = $removeFileParams ? $this->getTotalFileByType($params, $removeFileParams) : 0;

        return $totalNewFiles - $totalRemoveFiles;
    }

    /**
     * @param User   $context
     * @param array  $params
     * @param ?array $attrsName
     * @return void
     */
    protected function checkQuotaControlWhenCreateVideo(User $context, array $params, ?array $attrsName = null): void
    {
        $totalVideo = $this->getTotalFileNew($params, Arr::get($attrsName, 0), Arr::get($attrsName, 1));

        if ($totalVideo <= 0) {
            return;
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Video::ENTITY_TYPE, $totalVideo);
    }
}
