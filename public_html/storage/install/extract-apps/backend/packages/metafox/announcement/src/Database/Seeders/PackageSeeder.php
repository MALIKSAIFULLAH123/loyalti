<?php

namespace MetaFox\Announcement\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Announcement\Models\Style;

/**
 * Class PackageSeeder.
 * @codeCoverageIgnore
 * @ignore
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
        $this->styles();
    }

    /**
     * @todo update image path later
     */
    private function styles()
    {
        if (Style::query()->exists()) { // skip type seeder after installed.
            return;
        }

        $icons = [
            'success' => [
                'font'     => 'ico-check-circle-alt',
                'image'    => 'announcement-ico_success.png',
                'name_var' => 'announcement::phrase.success',
            ],
            'info' => [
                'image'    => 'announcement-ico_info.png',
                'font'     => 'ico-newspaper-o',
                'name_var' => 'announcement::phrase.info',
            ],
            'warning' => [
                'image'    => 'announcement-ico_warning.png',
                'font'     => 'ico-warning-o',
                'name_var' => 'announcement::phrase.warning',
            ],
            'danger' => [
                'image'    => 'announcement-ico_danger.png',
                'font'     => 'ico-fire',
                'name_var' => 'announcement::phrase.danger',
            ],
        ];

        foreach ($icons as $name => $icon) {
            $params = [
                'name'       => $name,
                'name_var'   => $icon['name_var'],
                'icon_image' => $icon['image'],
                'icon_font'  => $icon['font'],
            ];
            Style::firstOrCreate($params);
        }
    }
}
