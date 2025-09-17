<?php

namespace MetaFox\Localize\Http\Resources\v1;

use MetaFox\Localize\Repositories\LanguageRepositoryInterface;

class PackageSetting
{
    public function getMobileSettings(): array
    {
        return [
            'languages' => $this->getAvailableLanguages(),
        ];
    }

    public function getWebSettings(): array
    {
        $languages = $this->getAvailableLanguages();

        return [
            'languages'      => $languages,
            'total_language' => count($languages),
        ];
    }

    protected function getAvailableLanguages(): array
    {
        return resolve(LanguageRepositoryInterface::class)
            ->getOptions(true);
    }
}
