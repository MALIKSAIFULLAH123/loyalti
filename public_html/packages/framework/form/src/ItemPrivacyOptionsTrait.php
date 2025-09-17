<?php

namespace MetaFox\Form;

use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait ItemPrivacyOptionsTrait.
 */
trait ItemPrivacyOptionsTrait
{
    /**
     * get item privacy options for form field, the return array key is also the privacy value,
     * which can be converted to object in the API response.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItemPrivacyOptions(): array
    {
        $options = [];
        $privacyList = MetaFoxPrivacy::getItemPrivacy();

        foreach ($privacyList as $privacyValue => $phrase) {
            $options[$privacyValue] = [
                'label' => __p($phrase),
                'value' => $privacyValue,
            ];
        }

        return $options;
    }

    /**
     * get item privacy options for form field, the return array key is indexed sequentially,
     * which can be converted to array in the API response.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFieldItemPrivacyOptions(): array
    {
        return array_values($this->getItemPrivacyOptions());
    }
}
