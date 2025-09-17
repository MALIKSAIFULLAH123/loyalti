<?php

namespace MetaFox\Mux\Listeners;

use MetaFox\Platform\Facades\Settings;

class PackageInstalledListener
{
    /**
     * @param  string $package
     * @return void
     */
    public function handle(string $package): void
    {
        $this->migrateSiteSettingsFromVideo($package);
    }

    protected function migrateSiteSettingsFromVideo(string $package): void
    {
        if ($package !== 'metafox/mux') {
            return;
        }

        $oldSettings = [
            'video.mux.client_id'      => 'mux.video.client_id',
            'video.mux.client_secret'  => 'mux.video.client_secret',
            'video.mux.webhook_secret' => 'mux.video.webhook_secret',
        ];

        $keys = [];
        foreach ($oldSettings as $old => $new) {
            if (Settings::has($new)) {
                continue;
            }

            if (!Settings::has($old)) {
                continue;
            }

            Settings::save([$new => Settings::get($old, '')]);
            $keys[] = $old;
        }

        Settings::destroy('video', $keys);
    }
}
