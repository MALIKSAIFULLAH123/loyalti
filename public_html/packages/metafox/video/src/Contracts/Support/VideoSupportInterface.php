<?php

namespace MetaFox\Video\Contracts\Support;

use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video;

interface VideoSupportInterface
{
    /**
     * @param string $assetId
     */
    public function deleteVideoByAssetId(string $assetId): bool;

    /**
     * @param string $url
     * @return array<string, mixed>
     */
    public function parseLink(string $url): array;

    /**
     * @param string $content
     * @return string
     */
    public function parseVideoTitle(string $content): string;

    /**
     * @param Video $video
     * @return array
     */
    public function getStatusTexts(Video $video): array;

    /**
     * @return array
     */
    public function getMatureContentOptions(): array;

    /**
     * @return array
     */
    public function getMatureDataConfig(User $context, Video $video): ?array;

    public function getDataWithContext(User $user, Video $video, string $type = 'images');

    /**
     * @param VerifyProcess $model
     * @return array
     */
    public function getStatusVerifyProcessTexts(VerifyProcess $model): array;
}
