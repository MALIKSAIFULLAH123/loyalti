<?php

namespace MetaFox\User\Repositories\Eloquent\UserPrivacyTraits;

use MetaFox\Form\ItemPrivacyOptionsTrait;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait CollectItemPrivacySetting.
 *
 * Collect data from all apps. No database interaction.
 */
trait CollectItemPrivacySetting
{
    use ItemPrivacyOptionsTrait;

    /**
     * Collect {resource_name}.item_privacy.
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
    public function collectItemPrivacySetting(): array
    {
        $listeners = $this->massCollect('getDefaultPrivacy');

        $results = [];

        $defaultOptions = $this->getItemPrivacyOptions();

        foreach ($listeners as $moduleName => $moduleData) {
            foreach ($moduleData as $entityName => $data) {
                $privacyName = sprintf('%s' . MetaFoxConstant::SEPARATION_PERM . 'item_privacy', $entityName);
                $options     = $defaultOptions;
                if (isset($data['list'])) {
                    $options = [];
                    foreach ($data['list'] as $privacy) {
                        if (isset($defaultOptions[$privacy])) {
                            $options[] = $defaultOptions[$privacy];
                        }
                    }
                }

                $results[$privacyName] = [
                    'module_id' => $moduleName,
                    'phrase'    => $data['phrase'] ?? $privacyName,
                    'default'   => $data['default'] ?? MetaFoxPrivacy::EVERYONE,
                    'options'   => $options,
                ];
            }
        }

        return $results;
    }
}
