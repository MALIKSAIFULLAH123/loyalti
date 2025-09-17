<?php

namespace MetaFox\HealthCheck\Checks;

use MetaFox\Core\Support\Facades\License;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckLicense extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        $this->checkLicense($result);

        return $result;
    }

    private function checkLicense(Result $result)
    {
        if (License::isActive()) {
            return;
        }

        $title         = __p('health-check::web.license_inactive');
        $message       = __p('health-check::web.license_inactive_description', [
            'pricing_type' => Settings::get('core.license.pricing_type'),
            'error'        => Settings::get('core.license.error'),
        ]);

        $result->error($message, $title, $this->getActions());
    }

    private function getActions()
    {
        return [
            [
                'name'    => 'contact_us',
                'title'   => __p('health-check::phrase.contact_us'),
                'action'  => 'navigate',
                'payload' => [
                    'url'    => 'https://clients.phpfox.com/',
                    'target' => '_blank',
                ],
                'config' => [
                    'variant' => 'contained',
                    'color'   => 'primary',
                    'size'    => 'small',
                ],
            ],
            [
                'name'    => 'recheck',
                'title'   => __p('health-check::phrase.recheck'),
                'action'  => 'license/recheck',
                'payload' => [
                    'apiUrl'    => '/admincp/health-check/license',
                    'apiMethod' => 'POST',
                ],
                'config' => [
                    'variant' => 'link',
                    'size'    => 'small',
                ],
            ],
        ];
    }
}
