<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Str;

class SettingRouteListener
{
    public function handle(string $url): ?array
    {
        if (!Str::startsWith($url, 'settings/payment')) {
            return null;
        }

        return $this->handleSetting();
    }

    protected function handleSetting(): ?array
    {
        return [
            'path' => '/' . implode('/', ['user', 'settings', 'payment']),
        ];
    }
}
