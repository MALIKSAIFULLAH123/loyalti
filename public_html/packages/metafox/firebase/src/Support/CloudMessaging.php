<?php

namespace MetaFox\Firebase\Support;

use Google\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Facades\Settings;

class CloudMessaging
{
    public const FCM_URL_SEND_ENDPOINT         = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    public const FCM_URL_NOTIFICATION_ENDPOINT = 'https://fcm.googleapis.com/fcm/notification';

    /**
     * @var Client
     */
    protected Client $client;

    public function __construct()
    {
        $config = [
            'client_id'                           => Settings::get('firebase.client_id'),
            'client_email'                        => Settings::get('firebase.client_email'),
            'signing_key'                         => Settings::get('firebase.private_key'),
            'use_application_default_credentials' => true,
            'signing_algorithm'                   => 'HS256',
        ];

        $client = new Client($config);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $this->client = $client;
    }

    public function getAccessToken(): string|null
    {
        try {
            $token = $this->client->fetchAccessTokenWithAssertion();

            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return null;
        }
    }

    public function validatePushNotificationConfiguration(): bool
    {
        if (!Settings::get('firebase.project_id')) {
            return false;
        }

        if (!Settings::get('firebase.client_id')) {
            return false;
        }

        if (!Settings::get('firebase.client_email')) {
            return false;
        }

        if (!Settings::get('firebase.private_key')) {
            return false;
        }

        return (bool) $this->getAccessToken();
    }

    /**
     * @param  array<string, mixed> $data
     * @return bool
     */
    public function sendPushNotification(array $data): bool
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            /*
             * @deprecated Need to remove in some next version.
             */
            return (new CloudMessagingLegacy())->sendPushNotification($data);
        }

        $url = sprintf(self::FCM_URL_SEND_ENDPOINT, $this->getProjectId());

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $body   = Arr::get($data, 'bodyData', []);
        $tokens = Arr::get($data, 'tokens', []);

        $successfulSends = 0;

        foreach ($tokens as $token) {
            Arr::set($body, 'message.token', $token);

            $isSuccessful = $this->sendHttpRequest($url, $headers, $body);

            if ($isSuccessful) {
                $successfulSends++;
            }
        }

        return count($tokens) === $successfulSends;
    }

    private function sendHttpRequest(string $url, array $headers, array $body): bool
    {
        try {
            $response = Http::withHeaders($headers)
                ->withBody(json_encode($body) ?: '')
                ->post($url);

            Log::channel('push')->info('sendPushNotification v1', [
                'header'   => json_encode($headers),
                'body'     => $body,
                'response' => $response->body(),
            ]);

            return $response->successful();
        } catch (\Throwable $error) {
            Log::error('Error occurs when trying to call FCM API V1!');
            Log::error($error->getMessage());

            return false;
        }
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

    protected function getProjectId(): ?string
    {
        $getProjectId = Settings::get('firebase.project_id');

        return is_string($getProjectId) ? $getProjectId : null;
    }
}
