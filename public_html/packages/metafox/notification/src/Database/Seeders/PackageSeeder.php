<?php

namespace MetaFox\Notification\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Notification\Models\NotificationChannel;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedNotificationChannels();
    }

    public function seedNotificationChannels()
    {
        $channels = [
            [
                'name'      => 'database',
                'title'     => 'In-App',
                'is_system' => 1,
                'is_active' => 1,
            ],
            [
                'name'      => 'mail',
                'title'     => 'Email',
                'is_system' => 1,
                'is_active' => 1,
            ],
            [
                'name'      => 'sms',
                'title'     => 'SMS',
                'is_system' => 1,
                'is_active' => 1,
            ],
            [
                'name'      => 'webpush',
                'title'     => 'WebPush',
                'is_system' => 1,
                'is_active' => 1,
            ],
            [
                'name'      => 'mobilepush',
                'title'     => 'MobilePush',
                'is_system' => 1,
                'is_active' => 1,
            ],
        ];
        foreach ($channels as $channel) {
            NotificationChannel::query()->updateOrInsert(
                ['name' => Arr::get($channel, 'name', '')],
                $channel
            );
        }
    }
}
