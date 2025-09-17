<?php

namespace MetaFox\ChatPlus\Checks;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckNewChatplusServerVersion extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();
        if (!$this->compareVersions()) {
            return $result;
        }
        $actions = $this->getActions();
        $title   = $this->getTitle();
        $message = $this->getMessage();

        $result->warn($message, $title, $actions);

        return $result;
    }

    protected function compareVersions(): bool
    {
        $server = Settings::get('chatplus.server');
        if (!$server) {
            return false;
        }
        $apiUrl = $server . '/api/v1/chatplus/info';
        try {
            $info          = json_decode(mf_get_contents(base_path('packages/metafox/chatplus/composer.json')), true);
            $requireServer = Arr::get($info, 'extra.requireServer');
            $response      = Http::timeout(6)->get($apiUrl);
            if (!$response->successful()) {
                return false;
            }
            $serverVersion = $response->json('chatplus_server_version');
            if (version_compare($serverVersion, $requireServer, '<')) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }
    protected function getActions(): array
    {
        return [
            [
                'name'    => 'how_to_upgrade',
                'title'   => __p('chatplus::phrase.how_to_upgrade'),
                'action'  => 'navigate',
                'payload' => [
                    'url'    => 'https://docs.phpfox.com/display/MFMAN/Update+ChatPlus+server',
                    'target' => '_blank',
                ],
                'config' => [
                    'variant' => 'link',
                    'size'    => 'small',
                    'sx'      => [
                        'height' => 'auto',
                    ],
                ],
            ],
        ];
    }

    public function getName()
    {
        return __CLASS__;
    }

    protected function getTitle(): string
    {
        return  __p('chatplus::phrase.chatplus_server_incompatible');
    }

    protected function getMessage(): string
    {
        return __p('chatplus::phrase.chatplus_server_incompatible_desc');
    }
}
