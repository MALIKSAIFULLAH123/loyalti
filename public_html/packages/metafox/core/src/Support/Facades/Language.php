<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Localize\Contracts\LanguageSupportContract;
use MetaFox\Localize\Support\Language as LanguageSupport;
use MetaFox\Localize\Models\Language as Model;

/**
 * class Language.
 * @method static array                 getActiveOptions()
 * @method static string|null           getName(?string $id)
 * @method static array<string>         availableLocales()
 * @method static array<string>         getAllLocales()
 * @method static string                getDefaultLocaleId()
 * @method static array<string, mixed>  getAllActiveLanguages()
 * @method static Model                 getLanguage(string $languageId)
 * @method static array<int,    mixed>  extractPhraseData(string $key, array $data = [])
 * @method static array<string, mixed>  getPhraseValues(string $phraseKey)
 * @method static array<string, string> getEmptyPhraseData()
 * @method static void                  enableEditMode()
 * @method static void                  disableEditMode()
 * @see LanguageSupport
 */
class Language extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LanguageSupportContract::class;
    }
}
