<?php

namespace MetaFox\Form;

use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait PrivacyOptionsTrait.
 */
trait PrivacyOptionsTrait
{
    /**
     * get privacy options for form field, the return array key is also the privacy value,
     * which can be converted to object in the API response.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPrivacyOptions(): array
    {
        $options = [];

        $privacyList = MetaFoxPrivacy::getPrivacy();

        foreach ($privacyList as $privacyValue => $phrase) {
            $options[$privacyValue] = [
                'label' => __p($phrase),
                'value' => $privacyValue,
            ];
        }

        return $options;
    }

    /**
     * get privacy options for form field, the return array key is indexed sequentially,
     * which can be converted to array in the API response.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFieldPrivacyOptions(): array
    {
        return array_values($this->getPrivacyOptions());
    }
}
