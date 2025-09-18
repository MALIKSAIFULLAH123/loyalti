<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Repositories\VideoRepositoryInterface;

class VideoProcessingFailed
{
    public function __construct(protected VideoRepositoryInterface $videoRepository) { }

    public function handle(array $params): bool
    {
        return $this->videoRepository->failedProcessVideo($params);
    }
}
