<?php

namespace MetaFox\Mux\Support\Providers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Facades\RequestLifecycle;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Storage\Models\StorageFile;
use MuxPhp\Api\AssetsApi;
use MuxPhp\ApiException;
use MuxPhp\Configuration;
use MuxPhp\Models\Asset;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackPolicy;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ErrorControlOperator)
 */
class Mux implements VideoServiceInterface
{
    private string $clientId;

    private string $clientSecret;

    private string $moduleId;

    private ?string $handler;

    public string|null $webHookSecret = null;

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function setModuleId(string $moduleId): void
    {
        $this->moduleId = $moduleId;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
    }

    public function getWebHookSecret(): ?string
    {
        return $this->webHookSecret;
    }

    public function setWebHookSecret(?string $webHookSecret): void
    {
        $this->webHookSecret = $webHookSecret;
    }

    private string $videoPlayback;

    private string $thumbnailPlayback;

    private Configuration $config;

    public const PROVIDER_TYPE            = 'mux';
    public const MUX_WEBHOOK_HEADER       = 'Mux-Signature';
    public const MUX_STREAMING_PATH       = 'https://stream.mux.com';
    public const MUX_IMAGE_PATH           = 'https://image.mux.com';
    public const MUX_WEBHOOK_SETTING_PATH = 'https://dashboard.mux.com/settings/webhooks';

    public const VIDEO_ASSET_TYPE_READY                 = 'video.asset.ready';
    public const VIDEO_ASSET_TYPE_DELETED               = 'video.asset.deleted';
    public const VIDEO_ASSET_TYPE_ERROR                 = 'video.asset.errored';
    public const VIDEO_ASSET_TYPE_LIVE_STREAM_COMPLETED = 'video.asset.live_stream_completed';
    public const VIDEO_LIVE_STREAM_ACTIVE               = 'video.live_stream.active';
    public const VIDEO_LIVE_STREAM_IDLE                 = 'video.live_stream.idle';
    public const VIDEO_LIVE_STREAM_DISABLED             = 'video.live_stream.disabled';
    public const VIDEO_LIVE_STREAM_DELETED              = 'video.live_stream.deleted';
    public const VIDEO_LIVE_STREAM_DISCONNECTED         = 'video.live_stream.disconnected';

    public const BASE_MUX_URL = 'https://api.mux.com/video/v1/';

    public const LIVE_SERVER_URL = 'rtmps://global-live.mux.com:443/app';

    public function getProviderType(): string
    {
        return self::PROVIDER_TYPE;
    }

    /**
     * @param string      $moduleId
     * @param string|null $handler
     * @param bool        $throw
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(string $moduleId = 'video', string $handler = null, bool $throw = true)
    {
        $this->setClientId(Settings::get("mux.$moduleId.client_id", ''));
        $this->setClientSecret(Settings::get("mux.$moduleId.client_secret", ''));
        $this->setWebHookSecret(Settings::get("mux.$moduleId.webhook_secret", ''));
        $this->setModuleId($moduleId);
        $this->setHandler($handler);
        $this->initPlaybackUrl($moduleId);

        if (!$this->getClientId() || !$this->getClientSecret() || !$this->getWebHookSecret()) {
            $throw && abort(500, __p('mux::phrase.missing_service_configs'));
        }

        $this->config = Configuration::getDefaultConfiguration()
            ->setUsername($this->getClientId())
            ->setPassword($this->getClientSecret());
    }

    /**
     * @param  StorageFile          $file
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function processVideo(StorageFile $file): array
    {
        $url          = app('storage')->getUrl($file->entityId()) ?? '';
        $assetRequest = $this->makeRequest($url);

        $assetsApi = (new AssetsApi(
            new Client(),
            $this->getConfig()
        ))->createAsset($assetRequest);

        $data = $assetsApi->getData();

        if ($data instanceof Asset) {
            return [
                'asset_id'  => $data->getId(),
                'next_step' => 'webhook',
            ];
        }

        return [];
    }

    public function handleWebhook(Request $request): bool
    {
        if (!$this->getWebHookSecret()) {
            abort(500, __p('mux::phrase.missing_service_configs'));
        }

        /** @var string $requestContent */
        $requestContent = $request->getContent();
        $body           = json_decode($requestContent, true);

        // todo move to video app
        $data    = collect($body['data']);
        $assetId = (string) ($data->get('id', ''));

        $verified = $this->verifySignature($request);
        if (!$verified) {
            Log::info('Invalid Mux Webhook Signature!');
            Log::info('Header: ', $request->header());
            Log::info('Body: ', $body);

            $this->failProcessing(['asset_id' => $assetId]);

            return false;
        }

        if (!$this->getHandler()) {
            match ($body['type']) {
                self::VIDEO_ASSET_TYPE_READY   => $this->processReady($assetId, $data),
                self::VIDEO_ASSET_TYPE_DELETED => $this->processDeleted($assetId),
                self::VIDEO_ASSET_TYPE_ERROR   => $this->failProcessing(['asset_id' => $assetId]),
                default                        => true,
            };

            return true;
        }

        try {
            resolve($this->getHandler())->handleMuxWebhook($body);
        } catch (\Exception $e) {
            abort(500, 'Missing handler method: ' . $e->getMessage());
        }

        return true;
    }

    protected function processReady(string $assetId, Collection $data): void
    {
        $imagePath   = null;
        $videoPath   = null;
        $playbackIds = $data->get('playback_ids');

        if (!empty($playbackIds)) {
            $playbackId = $playbackIds[0]['id'];
            $videoPath  = self::MUX_STREAMING_PATH . DIRECTORY_SEPARATOR . $playbackId . '.m3u8';
            $imagePath  = self::MUX_IMAGE_PATH . DIRECTORY_SEPARATOR . $playbackId . DIRECTORY_SEPARATOR . 'thumbnail.jpg';
        }

        $thumbnail = $this->downloadThumbnail($assetId, $imagePath);
        $track     = [];
        $tracks    = $data->get('tracks');
        $params    = [
            'module_name'   => $this->getModuleId(),
            'in_process'    => 0,
            'resolution_x'  => null,
            'resolution_y'  => null,
            'duration'      => (int) $data->get('duration', 0),
            'destination'   => $videoPath,
            'image_file_id' => $thumbnail instanceof StorageFile ? $thumbnail->entityId() : 0,
        ];

        foreach ($tracks as $t) {
            if ('video' === $t['type']) {
                $track = $t;
                break;
            }
        }

        if (isset($track['max_width'])) {
            $params['resolution_x'] = $track['max_width'];
        }

        if (isset($track['max_height'])) {
            $params['resolution_y'] = $track['max_height'];
        }

        app('events')->dispatch('video.update_by_asset_id', [$assetId, $params], true);
    }

    protected function processDeleted(string $assetId): void
    {
        app('events')->dispatch('video.delete_by_asset_id', [$assetId], true);
    }

    protected function getConfig(): Configuration
    {
        return $this->config;
    }

    protected function makeRequest(string $url, string $policy = PlaybackPolicy::_PUBLIC): CreateAssetRequest
    {
        $input = new InputSettings(['url' => $url]);

        return new CreateAssetRequest([
            'input'           => [$input],
            'playback_policy' => $policy,
        ]);
    }

    protected function verifySignature(Request $request): bool
    {
        $header = $request->header(self::MUX_WEBHOOK_HEADER);
        $body   = $request->getContent();

        if (!is_string($header)) {
            return false;
        }

        $signatureArr = explode(',', $header);
        if (count($signatureArr) < 2) {
            return false;
        }

        $timestamp = Str::replaceFirst('t=', '', $signatureArr[0]);
        $hash      = Str::replaceFirst('v1=', '', $signatureArr[1]);

        $payload = $timestamp . '.' . $body;

        $ourSignature = hash_hmac('sha256', $payload, $this->getWebHookSecret());

        return hash_equals($ourSignature, $hash);
    }

    protected function downloadThumbnail(?string $assetId = null, ?string $realPath = null): ?StorageFile
    {
        if (!$realPath || !$assetId) {
            return null;
        }

        $tempFile = sprintf('%s_%s_thumbnail.jpg', tempnam(sys_get_temp_dir(), 'metafox'), Str::uuid());

        $content = mf_get_contents($realPath);
        file_put_contents($tempFile, !empty($content) ? $content : '');

        //  remove file before terminating.
        RequestLifecycle::onTerminated(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        });

        $uploadedFile = UploadFile::pathToUploadedFile($tempFile);
        if (!$uploadedFile) {
            return null;
        }

        $thumbnailFile = app('events')->dispatch('video.upload_thumbnail_by_asset_id', [$uploadedFile, $assetId], true);

        return $thumbnailFile instanceof StorageFile ? $thumbnailFile : null;
    }

    public function initPlaybackUrl(string $moduleId): void
    {
        $customVideoPlayback     = Settings::get("$moduleId.custom_video_playback_url");
        $customThumbPlayback     = Settings::get("$moduleId.custom_thumbnail_playback_url");
        $this->videoPlayback     = !empty($customVideoPlayback) ? $customVideoPlayback : self::MUX_STREAMING_PATH;
        $this->thumbnailPlayback = !empty($customThumbPlayback) ? $customThumbPlayback : self::MUX_IMAGE_PATH;
    }

    /**
     * @return string
     */
    public function getVideoPlayback(): string
    {
        return $this->videoPlayback;
    }

    /**
     * @return string
     */
    public function getThumbnailPlayback(): string
    {
        return $this->thumbnailPlayback;
    }

    /**
     * @param  string     $apiName
     * @param  string     $method
     * @param  bool       $returnTransfer
     * @param  string     $postFields
     * @return bool|mixed
     */
    public function executeApi(string $apiName, string $method = 'GET', bool $returnTransfer = false, string $postFields = ''): mixed
    {
        $sUrl = self::BASE_MUX_URL . $apiName;

        if (empty($this->getClientId()) || empty($this->getClientSecret())) {
            return false;
        }
        $curl = curl_init($sUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->getClientId() . ':' . $this->getClientSecret()),
        ]);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $returnTransfer);
        curl_setopt($curl, CURLOPT_PUT, $method == 'PUT');
        curl_setopt($curl, CURLOPT_POST, $method == 'POST');
        if (!empty($postFields)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        if (!empty($err) || empty($response)) {
            return false;
        }
        $response = json_decode($response, true);

        if (!empty($response['error'])) {
            return false;
        }

        return $returnTransfer ? $response['data'] : true;
    }

    public function getLiveServerUrl(): string
    {
        return self::LIVE_SERVER_URL;
    }

    public function isValidConfiguration(): bool
    {
        return $this->getClientId() && $this->getClientSecret() && $this->getWebHookSecret();
    }

    public function failProcessing(array $params): void
    {
        app('events')->dispatch('video.processing_failed', [$params], true);
    }

    public function testConfig(): bool
    {
        return $this->isMuxConfigValid();
    }

    protected function isMuxConfigValid(): bool
    {
        $assetsApi = new AssetsApi(new Client(), $this->getConfig());

        try {
            [, $statusCode] = $assetsApi->listAssetsWithHttpInfo();

            return $statusCode == 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
