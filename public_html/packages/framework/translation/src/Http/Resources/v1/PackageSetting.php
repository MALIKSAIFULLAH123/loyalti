<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Translation\Http\Resources\v1;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Translation\Repositories\TranslationServiceRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'enable_translate' => $this->checkConfigTranslation(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'enable_translate' => $this->checkConfigTranslation(),
        ];
    }

    private function checkConfigTranslation(): bool
    {
        return Settings::get('translation.enable_translate') &&
            resolve(TranslationServiceRepositoryInterface::class)->checkConfigTranslation();
    }
}
