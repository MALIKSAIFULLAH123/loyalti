<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Yup\Shape;
use MetaFox\Yup\Yup;

class TranslatableTextField extends Section
{
    private string $label       = '';
    private string $description = '';

    private ?AbstractField $defaultComponent = null;

    private ?string $componentText;

    private ?Shape $validator;

    private array $forwardCalls = [];

    protected bool   $original  = false;
    protected string $keyPhrase = '';

    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        $this->componentText = 'text';
        $this->validator     = null;
    }

    public function initialize(): void
    {
        parent::initialize();

        $this->sx(['mb' => 2]);
    }

    /**
     * NOTE: This method must be use lastly in order to other setAttribute to taking affect.
     */
    public function buildFields(): self
    {
        $default                = Language::getDefaultLocaleId();
        $defaultLanguage        = Language::getLanguage($default);
        $name                   = $this->getName();
        $this->defaultComponent = $this->getComponent(sprintf('%s.%s', $name, $defaultLanguage->language_code));
        $description            = $this->buildDescription(null);

        if ($this->original && !empty($this->keyPhrase)) {
            $defaultText = $this->getPhraseValueDefaultByLocale($default);

            $description = $description . PHP_EOL . __p('localize::phrase.text_value_desc', [
                    'key'          => $this->keyPhrase,
                    'default_text' => $defaultText,
                ]);
        }
        $disabled = (bool) $this->getAttribute('disabled', false);;

        $defaultComponent = $this->defaultComponent();
        $defaultComponent->required((bool) $this->getAttribute('required'));
        $defaultComponent->disabled($disabled);

        if (!empty($this->getAttribute('requiredWhen', []))) {
            $defaultComponent->requiredWhen($this->getAttribute('requiredWhen', []));
            $defaultComponent->removeAttribute('required');
            $this->removeAttribute('requiredWhen');
        }

        if ($this->validation == null) {
            $defaultComponent->yup($this->getYup());
        }

        $this->addField(
            $defaultComponent
                ->fullWidth(false)
                ->marginDense()
                ->label($this->label)
                ->description($description)
        );

        $languages = Language::getAllActiveLanguages();
        if (count($languages) == 1) {
            return $this;
        }

        foreach ($languages as $locale => $language) {
            if ($locale === $default) {
                continue;
            }

            $this->addField(
                $this->getComponent(sprintf('%s.%s', $name, $language->language_code))
                    ->fullWidth(false)
                    ->marginDense()
                    ->disabled($disabled)
                    ->label(__p('localize::phrase.name_in_language_name', ['name' => $this->label, 'language' => $language->name]))
                    ->showWhen(['truthy', sprintf('%s.%s', $name, 'use_custom_language')])
            );
        }

        $this->addField(
            Builder::viewMore(sprintf('%s.%s', $name, 'use_custom_language'))
                ->marginDense()
                ->viewMoreText(__p('localize::phrase.name_in_other_language', ['name' => $this->label]))
                ->viewLessText(__p('localize::phrase.use_default'))
        );

        return $this;
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * get validation.
     *
     * @return Shape
     */
    public function getYup(): Shape
    {
        $validator = $this->validator;
        if (null != $validator) {
            return $validator;
        }

        $validator = Yup::string();
        if ($this->getAttribute('required', false)) {
            $validator->required();
        }

        return $validator;
    }

    public function yup(Shape $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    protected function getComponent(string $name): AbstractField
    {
        $creator   = Builder::getCreator($this->componentText);
        $component = new $creator(['name' => $name]);

        foreach ($this->forwardCalls as $method => $args) {
            $component->$method($args);
        }

        return $component;
    }

    public function asTextArea(): self
    {
        $this->componentText = 'textArea';

        return $this;
    }

    public function asTextEditor(): self
    {
        $this->componentText = 'richTextEditor';

        return $this;
    }

    public function defaultComponent(): ?AbstractField
    {
        return $this->defaultComponent;
    }

    public function forward(string $method, mixed $args): self
    {
        $this->forwardCalls[$method] = $args;

        return $this;
    }

    protected function buildDescription(?string $language): string
    {
        if (!$this->description) {
            return __p('localize::phrase.translatable_text_desc');
        }

        if ($language === null) {
            return $this->description;
        }

        return __p('localize::phrase.desc_in_language_name', [
            'desc'     => $this->description,
            'language' => $language,
        ]);
    }

    public function originalText(string $keyPhrase, bool $original): static
    {
        $this->original  = $original;
        $this->keyPhrase = $keyPhrase;
        return $this;
    }

    private function getPhraseValueDefaultByLocale(string $language): ?string
    {
        /**@var PhraseRepositoryInterface $phraseRepository */
        $phraseRepository = resolve(PhraseRepositoryInterface::class);

        $model = $phraseRepository->getPhrasesByKey($this->keyPhrase)
            ->where('locale', $language)
            ->first();

        return $model?->default_text;
    }

    /**
     * @param Shape $validator
     * @return $this
     */
    public function validation(Shape $validator): static
    {
        $this->validation = $validator;

        return $this;
    }
}
