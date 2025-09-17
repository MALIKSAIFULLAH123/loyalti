<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Resources\v1;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    /**
     * @return array<string, mixed>
     */
    public function getWebSettings(): array
    {
        $staticPageUrl = resolve(StaticPageRepositoryInterface::class)
            ->getStaticPageUrlById(Settings::get('core.offline_static_page'));

        return [
            'adminHomePages'          => app('core.packages')->getInternalAdminUrls(),
            'metafox_news_url'        => 'https://www.phpfox.com/blog/',
            'offline'                 => file_exists(base_path('storage/framework/down')),
            'offline_static_page_url' => $staticPageUrl,
            'version'                 => MetaFox::getVersion(),
            'file_mime_type_accepts'  => [
                'image' => file_type()->getAllowableTypes('photo'),
                'video' => file_type()->getAllowableTypes('video'),
            ],
            'pwa'        => $this->getPwaSettings(),
            //'allow_html' => Settings::get('core.general.allow_html', true),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'version'           => MetaFox::getVersion(),
            'menu_type_setting' => Settings::get('core.menu_layout_setting', MetaFoxConstant::LAYOUT_AS_LIST),
        ];
    }

    protected function getPwaSettings(): array
    {
        return [
            'enable'              => (bool) Settings::get('core.pwa.enable'),
            'app_name'            => Settings::get('core.pwa.app_name'),
            'app_description'     => Settings::get('core.pwa.app_description'),
            'install_description' => Settings::get('core.pwa.install_description'),
        ];
    }
}
