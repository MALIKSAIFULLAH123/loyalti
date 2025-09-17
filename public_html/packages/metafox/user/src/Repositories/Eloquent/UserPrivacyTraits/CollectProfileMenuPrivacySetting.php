<?php

namespace MetaFox\User\Repositories\Eloquent\UserPrivacyTraits;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait CollectProfileMenuPrivacySetting.
 *
 * Collect data from all apps. No database interaction.
 */
trait CollectProfileMenuPrivacySetting
{
    use UserPrivacyOptions;

    /**
     * Collect {resource_name}.profile_menu.
     * [
     *      'privacy_name' => [
     *            'phrase' => 'abc',
     *            'default' => 0,
     *            'list' => [
     *                  0 => Everyone,
     *                  1 => Friend,
     *            ]
     *       ]
     * ].
     * @return array<string, mixed>
     */
    public function collectProfileMenuSetting(): array
    {
        $listeners      = $this->massCollect('getProfileMenu');
        $results        = [];
        $defaultOptions = $this->getUserPrivacyOptions('user');

        foreach ($listeners as $moduleName => $moduleData) {
            foreach ($moduleData as $entityName => $data) {
                $privacyName = sprintf('%s' . MetaFoxConstant::SEPARATION_PERM . 'profile_menu', $entityName);

                $options = $defaultOptions;
                if (isset($data['list'])) {
                    $options = [];
                    foreach ($data['list'] as $privacy) {
                        if (isset($defaultOptions[$privacy])) {
                            $options[] = $defaultOptions[$privacy];
                        }
                    }
                }

                $results[$privacyName] = [
                    'module_id'   => $moduleName,
                    'phrase'      => $data['phrase'] ?? $privacyName,
                    'default'     => $data['default'] ?? MetaFoxPrivacy::EVERYONE,
                    'options'     => $options,
                    'is_editable' => $data['is_editable'] ?? true,
                ];
            }
        }

        return $results;
    }
}
