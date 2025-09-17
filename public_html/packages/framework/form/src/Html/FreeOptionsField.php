<?php
namespace MetaFox\Form\Html;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Localize\Models\Language;
use MetaFox\Core\Support\Facades\Language as LanguageFacade;

class FreeOptionsField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FREE_OPTIONS)
            ->name('options')
            ->variant('outlined')
            ->fullWidth()
            ->marginNormal()
            ->sizeMedium()
            ->label(__p('core::web.options'));
    }

    public function translatable(): static
    {
        /**
         * @var Collection $languages
         */
        $languages = resolve(LanguageRepositoryInterface::class)->getActiveLanguages();

        if (!$languages->count()) {
            return $this;
        }

        $defaultLanguage = LanguageFacade::getDefaultLocaleId();

        $options = $languages->map(function (Language $language) use ($defaultLanguage) {
            return [
                'label' => $language->name,
                'value' => $language->language_code,
                'required' => $defaultLanguage === $language->language_code,
            ];
        })->toArray();

        usort($options, function ($a, $b) {
            if ($a['required']) {
                return -1;
            }

            return 1;
        });

        return $this->setAttribute('translatableOptions', array_values($options));
    }

    public function sortable(bool $value = true): static
    {
        return $this->setAttribute('sortable', $value);
    }
}
