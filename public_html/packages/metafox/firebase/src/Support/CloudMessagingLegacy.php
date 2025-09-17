<?php

namespace MetaFox\Firebase\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Facades\Settings;

/**
 * @deprecated Need to remove in some next version.
 */
class CloudMessagingLegacy
{
    public const FCM_URL_SEND_ENDPOINT         = 'https://fcm.googleapis.com/fcm/send';
    public const FCM_URL_NOTIFICATION_ENDPOINT = 'https://fcm.googleapis.com/fcm/notification';

    /**
     * @param  array<string, mixed> $data
     * @return bool
     */
    public function sendPushNotification(array $data): bool
    {
        $serverKey = $this->getServerKey();
        $tokens    = Arr::get($data, 'tokens', []);
        $bodyData  = Arr::get($data, 'bodyData', []);
        $fields    = [
            'registration_ids' => $tokens,
            'priority'         => 'high',
            ...$bodyData,
        ];

        $headers = [
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ];

        $body = json_encode($fields);

        $response = null;
        try {
            $response = Http::withHeaders($headers)
                ->withBody($body ?: '', 'application/json')
                ->post(self::FCM_URL_SEND_ENDPOINT);

            Log::channel('push')->info('sendPushNotification', [
                'header'   => json_encode($headers),
                'body'     => $body,
                'response' => $response->body(),
            ]);
        } catch (\Throwable $error) {
            Log::error('Error occurs when trying to call FCM API!');
            Log::error($error->getMessage());
        }

        if ($response instanceof Response) {
            return $response->successful();
        }

        return false;
    }

    /**
     * @param  int           $userId
     * @param  array         $devices
     * @param  array<string> $tokens
     * @param  string        $platform
     * @return bool
     */
    public function addUserDeviceGroup(int $userId, array $devices, array $tokens = [], string $platform = 'mobile'): bool
    {
        if (!$this->getServerKey()) {
            app('events')->dispatch('firebase.device_tokens.add', [$userId, $devices, $tokens, $platform]);

            return true;
        }

        $headers             = $this->getNotificationHeaders();
        $notificationKeyName = match ($platform) {
            'web'   => sprintf('web-user-%s', $userId),
            default => sprintf('user-%s', $userId),
        };
        $notificationKey = $this->getUserDeviceGroup($notificationKeyName);

        $data = [
            'operation'             => $notificationKey ? 'add' : 'create',
            'notification_key_name' => $notificationKeyName,
            'registration_ids'      => $tokens,
        ];

        if ($notificationKey) {
            $data['notification_key'] = $notificationKey;
        }

        $body = json_encode($data);

        $response = Http::withHeaders($headers)
            ->withBody($body ?: '', 'application/json')
            ->post(self::FCM_URL_NOTIFICATION_ENDPOINT);

        Log::channel('push')->info('addUserDeviceGroup', [
            'header'   => $headers,
            'body'     => $body,
            'response' => $response->body(),
        ]);

        if ($response->successful()) {
            return true;
        }

        app('events')->dispatch('firebase.device_tokens.add', [$userId, $devices, $tokens, $platform]);

        return true;
    }

    /**
     * @param  int           $userId
     * @param  array<string> $tokens
     * @return bool
     */
    public function removeUserDeviceGroup(int $userId, array $tokens = []): bool
    {
        if (!$this->getServerKey()) {
            app('events')->dispatch('firebase.device_tokens.remove', [$userId, $tokens]);

            return true;
        }

        $keys = ["web-user-$userId", "user-$userId"];

        foreach ($keys as $key) {
            $result = $this->removeUserDeviceGroupByKey($key, $tokens);

            if (!$result) {
                app('events')->dispatch('firebase.device_tokens.remove', [$userId, $tokens]);

                return true;
            }
        }

        return true;
    }

    /**
     * @param  string      $keyName
     * @return string|null
     */
    public function getUserDeviceGroup(string $keyName): ?string
    {
        $query = [
            'notification_key_name' => $keyName,
        ];

        $response = Http::withHeaders($this->getNotificationHeaders())
            ->get(self::FCM_URL_NOTIFICATION_ENDPOINT, $query);

        if (!$response->successful()) {
            return null;
        }

        return $response->json('notification_key');
    }

    protected function getServerKey(): ?string
    {
        $key = Settings::get('firebase.server_key');

        return is_string($key) ? $key : null;
    }

    protected function getSenderId(): ?string
    {
        $senderID = Settings::get('firebase.sender_id');

        return is_string($senderID) ? $senderID : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getNotificationHeaders(): array
    {
        return [
            'Authorization' => 'key=' . $this->getServerKey(),
            'project_id'    => $this->getSenderId(),
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * @param  string        $keyName
     * @param  array<string> $tokens
     * @return bool
     */
    public function removeUserDeviceGroupByKey(string $keyName, array $tokens = []): bool
    {
        $notificationKey = $this->getUserDeviceGroup($keyName);
        if (!$notificationKey) {
            return false;
        }

        $headers = $this->getNotificationHeaders();

        $data = [
            'operation'             => 'remove',
            'notification_key'      => $notificationKey,
            'notification_key_name' => $keyName,
            'registration_ids'      => $tokens,
        ];

        $body = json_encode($data);

        $response = Http::withHeaders($headers)
            ->withBody($body ?: '', 'application/json')
            ->post(self::FCM_URL_NOTIFICATION_ENDPOINT);

        Log::channel('push')->info('removeUserDeviceGroupByKey', [
            'header'   => $headers,
            'body'     => $body,
            'response' => $response->body(),
        ]);

        if ($response->successful()) {
            return true;
        }

        return false;
    }
}
