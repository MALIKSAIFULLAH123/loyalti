<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\GroupPrivacy;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\PackageManager;

class PrivacyProfileSettingListener
{
    public function handle(array &$resourceSettings, string $entityType): void
    {
        if ($entityType != Group::ENTITY_TYPE) {
            return;
        }

        $settings = Arr::get($resourceSettings, 'core:view_members');
        if (!is_array($settings)) {
            return;
        }

        $privacyResource = PackageManager::discoverSettings('getUserPrivacyResource');

        if (empty($privacyResource) || !is_array($privacyResource)) {
            return;
        }

        $groupPrivacy    = new GroupPrivacy();
        $privacyResource = Arr::get($privacyResource, "$entityType.$entityType");
        $privacyResource = Arr::get($privacyResource, 'core.view_members');
        $privacyList     = Arr::get($privacyResource, 'list');
        $generalSettings = MetaFoxPrivacy::getUserPrivacy();
        $defaultOptions  = $groupPrivacy->getPrivacyOptionsPhrase();
        $options         = [];

        foreach ($defaultOptions as $key => $value) {
            Arr::set($generalSettings, $key, $value);
        }

        foreach ($privacyList as $privacy) {
            if (Arr::has($generalSettings, $privacy)) {
                $options[$privacy] = [
                    'value' => $privacy,
                    'label' => __p(Arr::get($generalSettings, $privacy)),
                ];
            }
        }

        Arr::set($settings, 'options', $options);
        Arr::set($resourceSettings, 'core:view_members', $settings);
    }
}
