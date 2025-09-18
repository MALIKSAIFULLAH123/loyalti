<?php

namespace MetaFox\Video\Traits;

use Illuminate\Support\Arr;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Models\Video;

trait CheckVideoServiceTrait
{
    protected function checkVideoService(array $params, string $key): void
    {
        $files           = Arr::get($params, $key, []);
        $collectionFiles = collect($files)->groupBy('type')->map->count();
        $hasVideo        = (bool) Arr::get($collectionFiles, Video::ENTITY_TYPE, 0);

        if (!$hasVideo) {
            return;
        }

        if (!$this->isVideoUrlProvided($params)) {
            $this->abortIfVideoServiceNotReady();
        }
    }

    private function isVideoUrlProvided(array $params): bool
    {
        return Arr::get($params, 'video_url') != null;
    }

    private function abortIfVideoServiceNotReady(): void
    {
        $providerManager = resolve(ProviderManagerInterface::class);

        if (!$providerManager->checkReadyService()) {
            abort(400, __p('video::phrase.please_configure_the_video_service'));
        }
    }
}
