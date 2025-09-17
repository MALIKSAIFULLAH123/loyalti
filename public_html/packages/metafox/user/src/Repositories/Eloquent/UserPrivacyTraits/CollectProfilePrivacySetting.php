<?php

namespace MetaFox\User\Repositories\Eloquent\UserPrivacyTraits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait CollectProfilePrivacySetting.
 *
 * Collect data from all apps. No database interaction.
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
trait CollectProfilePrivacySetting
{
    use UserPrivacyOptions;

    /**
     * Collect raw data from app user privacy settings.
     * [
     *  'profile.view_profile' => [
     *      'phrase' => 'Who can view your profile page?',
     *      'default' => 0,
     *   ],
     * ].
     * @return array<string, mixed>
     */
    private function collectUserPrivacyType(): array
    {
        $results  = [];
        $response = $this->massCollect('getUserPrivacy');

        foreach ($response as $privacyData) {
            if (!empty($privacyData)) {
                foreach ($privacyData as $privacyName => $data) {
                    if (!is_string($privacyName) || empty($data)) {
                        continue;
                    }

                    $privacyName           = str_replace('.', MetaFoxConstant::SEPARATION_PERM, $privacyName);
                    $results[$privacyName] = [
                        'phrase'  => $data['phrase'] ?? $privacyName,
                        'default' => $data['default'] ?? MetaFoxPrivacy::EVERYONE,
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * This method will help you collect all privacy belongs to a resource.
     *
     * [
     *      'group' => [
     *          'privacy_name' => [
     *            'phrase' => 'abc',
     *            'default' => 0,
     *            'list' => [
     *                 MetaFoxPrivacy::EVERYONE,
     *                 MetaFoxPrivacy::FRIENDS
     *             ],
     *          ],
     *       ],
     *      'page' => [],
     *      'user' => [],
     * ].
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function collectProfilePrivacySetting(): array
    {
        $privacyTypes = $this->collectUserPrivacyType();

        $listeners = $this->massCollect('getUserPrivacyResource');

        $results = [];

        $defaultOptions = [];

        $defaultPrivates = [];

        $generalSettings = MetaFoxPrivacy::getUserPrivacy();

        foreach ($listeners as $moduleName => $module) {
            foreach ($module as $entityType => $settings) {
                if (!isset($defaultOptions[$entityType])) {
                    $defaultOptions[$entityType] = $this->getUserPrivacyOptions($entityType);
                }

                if (!isset($defaultPrivates[$entityType])) {
                    $defaultPrivates[$entityType] = $this->getDefaultPrivacy($entityType);
                }

                foreach ($settings as $key => $value) {
                    /**
                     * @description support ['setting_name'] or ['setting_name' => ['phrase' => phrase, 'list'=> [MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::FRIENDS]]].
                     */
                    $typeId = is_string($key) ? $key : $value;
                    $typeId = str_replace('.', MetaFoxConstant::SEPARATION_PERM, $typeId);

                    if (!array_key_exists($typeId, $privacyTypes)) {
                        continue;
                    }

                    $result = [
                        'options' => $defaultOptions[$entityType],
                        'default' => $defaultPrivates[$entityType],
                    ];

                    if (is_array($value)) {
                        $result['phrase'] = $value['phrase'] ?? $privacyTypes[$typeId]['phrase'];

                        if (isset($value['default'])) {
                            $result['default'] = $value['default'];
                        }

                        if (isset($value['list'])) {
                            $options = [];

                            foreach ($value['list'] as $privacy) {
                                if (isset($generalSettings[$privacy])) {
                                    $options[$privacy] = [
                                        'value' => $privacy,
                                        'label' => __p($generalSettings[$privacy]),
                                    ];
                                }
                            }

                            if (count($options)) {
                                $result['options'] = $options;
                            }
                        }
                    }

                    $result['module_id'] = $moduleName;

                    $results[$entityType][$typeId] = array_merge($privacyTypes[$typeId], $result);
                }
            }
        }

        return $results;
    }

    public function collectProfilePrivacySettingByEntity(string $entityType): array
    {
        $results = $this->collectProfilePrivacySetting();

	    $resourceSettings = Arr::get($results, $entityType);

		if (!is_array($resourceSettings)) {
			return [];
		}

		try {
			app('events')->dispatch('user.privacy.profile_settings', [&$resourceSettings, $entityType]);
		} catch (\Throwable $exception) {
			Log::error('override profile setting error message: ' . $exception->getMessage());
			Log::error('override profile setting error trace: ' . $exception->getTraceAsString());
		}

        return $resourceSettings;
    }
}
