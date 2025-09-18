<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\UserStreamKey;
use MetaFox\LiveStreaming\Models\UserStreamKey as Model;
use MetaFox\LiveStreaming\Repositories\UserStreamKeyRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserStreamKeyRepository.
 */
class UserStreamKeyRepository extends AbstractRepository implements UserStreamKeyRepositoryInterface
{
    use RepoTrait;

    public const SERVICE_MUX = 'mux';

    public function model()
    {
        return Model::class;
    }

    public function getUserStreamKey(ContractUser $context): mixed
    {
        $key = $this->getModel()->newQuery()
            ->where([
                'user_id'      => $context->userId(),
                'user_type'    => $context->userType(),
                'is_streaming' => 0,
            ])->first();
        $service       = $this->getServiceManager();
        $serviceName   = $service->getDefaultServiceName();
        $serviceDriver = $service->getDefaultServiceProvider();

        if (!$serviceDriver instanceof VideoServiceInterface) {
            return null;
        }

        if (!$key) {
            $key = $this->createUserStreamKey($context, $serviceDriver);
        } else {
            if ($serviceName == self::SERVICE_MUX) {
                $stream = $serviceDriver->executeApi('live-streams/' . $key->live_stream_id, 'GET', true);
                if (empty($stream) || $stream['status'] !== 'idle' || !empty($stream['recording'])) {
                    //Should generate new one
                    if (empty($stream) || $stream['status'] == 'disabled') {
                        $this->delete($key->id);
                    }
                    $key = $this->createUserStreamKey($context, $serviceDriver);
                }
            }
        }

        return $key;
    }

    public function createUserStreamKey(ContractUser $user, VideoServiceInterface $serviceDriver): mixed
    {
        $field  = '{ "playback_policy": "public", "new_asset_settings": { "playback_policy": "public" }, "reduced_latency": ' . (Settings::get('mux.livestreaming.reduced_latency') ? 'true' : 'false') . ' }';
        $result = $serviceDriver->executeApi('live-streams', 'POST', true, $field);
        if (!$result) {
            return null;
        }

        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();

        $model->fill([
            'asset_id'       => $result['asset_id'] ?? '',
            'stream_key'     => $result['stream_key'],
            'live_stream_id' => $result['id'],
            'playback_ids'   => isset($result['playback_ids']) ? serialize($result['playback_ids']) : '',
            'is_streaming'   => isset($result['status']) && $result['status'] == 'active',
            'user_id'        => $user->userId(),
            'user_type'      => $user->userType(),
            'created_at'     => now(),
        ]);

        $model->save();
        $model->refresh();

        app('firebase.firestore')->addDocument(LiveVideoRepository::FIREBASE_COLLECTION, $result['stream_key'], [
            'stream_key' => $result['stream_key'],
        ], 'idle');

        return $model;
    }

    public function updateUserStreamKey(string $streamKey, string $type, ?LiveVideo $liveVideo = null): bool
    {
        $liveVideoRepository    = $this->getLiveVideoRepository();
        $playbackDataRepository = $this->getPlaybackDataRepository();
        $service                = $this->getServiceManager();
        $defaultService         = $service->getDefaultService();
        /** @var UserStreamKey $streamKeyModel */
        $streamKeyModel = UserStreamKey::query()
            ->where('stream_key', $streamKey)
            ->where('is_streaming', 0)
            ->first();
        if (!$streamKeyModel) {
            return false;
        }
        $ignoreUpdateVideo = true;
        $status            = '';
        if ($defaultService->driver == self::SERVICE_MUX) {
            $className = $defaultService->service_class;
            switch ($type) {
                case $className::VIDEO_LIVE_STREAM_IDLE:
                case $className::VIDEO_LIVE_STREAM_DISCONNECTED:
                    $streamKeyModel->is_streaming   = 0;
                    $streamKeyModel->connected_from = 0;
                    $streamKeyModel->save();
                    $status = LiveVideoRepository::STATUS_IDLE;
                    break;
                case LiveVideoRepository::ACTION_USER_GO_LIVE:
                    $streamKeyModel->is_streaming = 1;
                    $streamKeyModel->save();
                    break;
                case $className::VIDEO_LIVE_STREAM_ACTIVE:
                case LiveVideoRepository::ACTION_USER_GO_LIVE_WEBCAM:
                    $status                         = LiveVideoRepository::STATUS_WAITING;
                    $streamKeyModel->connected_from = time();
                    $streamKeyModel->save();
                    $this->getLiveVideoRepository()->validateLimitTime($streamKeyModel->user, $streamKeyModel->live_stream_id, $liveVideo);
                    break;
                default:
                    $streamKeyModel->delete();
                    $ignoreUpdateVideo = false;
                    $status            = LiveVideoRepository::STATUS_DELETED;
                    break;
            }
        } else {
            [$ignoreUpdateVideo, $status] = app('events')->dispatch('livestreaming.update_user_stream_key', [$type, $streamKeyModel, $liveVideo], true);
        }
        //Delete temporary live video of this stream key
        if (!empty($liveVideo) && $liveVideo->view_id == 2) {
            if (!$ignoreUpdateVideo) {
                $liveVideoRepository->delete($liveVideo->id);
                $playbackDataRepository->delete($liveVideo->id);
            }
            if (!empty($status)) {
                $liveVideoRepository->saveToFirebase($liveVideo, $status);
            }
        }

        return true;
    }
}
