<?php

namespace MetaFox\Story\Support\VideoServices;

use Illuminate\Http\Request;
use MetaFox\Mux\Support\Providers\Mux as BaseMux;
use MetaFox\Storage\Models\StorageFile;

/**
 * @deprecated 5.1.10 or higher
 * Will be removed in version 5.1.10 or higher
 */
class Mux extends BaseMux
{
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
        $assetId = $data->get('id');

        $verified = $this->verifySignature($request);
        if (!$verified) {
            $this->failProcessing(['asset_id' => $assetId]);

            return false;
        }

        if ($this->getHandler()) {
            try {
                resolve($this->getHandler())->handleMuxWebhook($body);
            } catch (\Exception $e) {
                abort(500, 'Missing handler method: ' . $e->getMessage());
            }
        } else {
            switch ($body['type']) {
                case self::VIDEO_ASSET_TYPE_READY:
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
                    $duration  = $data->get('duration');
                    $params    = [
                        'module_name'   => $this->getModuleId(),
                        'in_process'    => 0,
                        'resolution_x'  => null,
                        'resolution_y'  => null,
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

                    $params['duration'] = (int) $duration;

                    app('events')->dispatch('video.update_by_asset_id', [$assetId, $params], true);
                    break;
                case self::VIDEO_ASSET_TYPE_DELETED:
                    app('events')->dispatch('video.delete_by_asset_id', [$assetId], true);
                    break;
                case self::VIDEO_ASSET_TYPE_ERROR:
                    $this->failProcessing(['asset_id' => $assetId]);
                    break;
                default:
                    return true;
            }
        }

        return true;
    }
}
