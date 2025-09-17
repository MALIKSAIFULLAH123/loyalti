<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Http\Request;
use MetaFox\Storage\Models\StorageFile;

/**
 * class VideoServiceInterface.
 */
interface VideoServiceInterface
{
    /**
     * @param  StorageFile          $file
     * @return array<string, mixed>
     */
    public function processVideo(StorageFile $file): array;

    public function handleWebhook(Request $request): bool;

    public function getProviderType(): string;

    public function executeApi(string $apiName, string $method = 'GET', bool $returnTransfer = false, string $postFields = ''): mixed;

    public function getLiveServerUrl(): string;

    public function getThumbnailPlayback(): string;

    public function getVideoPlayback(): string;

    public function isValidConfiguration(): bool;

    public function failProcessing(array $params): void;

    public function testConfig(): bool;
}
