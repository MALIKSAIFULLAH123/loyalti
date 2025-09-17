<?php

namespace MetaFox\Localize\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Rules\AllowInRule;

class TranslatableTextRule implements RuleContract
{
    protected bool $sometimes;

    public function __construct(bool $sometimes = false)
    {
        $this->sometimes = $sometimes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return true;
        }

        $default         = Language::getDefaultLocaleId();
        $defaultRules    = array_merge($this->sometimes ? ['sometimes', 'nullable'] : ['required'], ['bail', 'string']);
        $defaultLanguage = Language::getLanguage($default);

        $rules = [
            "$attribute.$default" => $defaultRules,
            'use_custom_language' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];

        $label     = __p('localize::phrase.name_in_language_name', ['name' => $attribute, 'language' => $defaultLanguage->name]);
        $validator = Validator::make([$attribute => $value], $rules, [
            "$attribute.$default.required" => __p('validation.required', ['attribute' => $label]),
            "$attribute.$default.string"   => __p('validation.required', ['attribute' => $label]),
        ]);

        $validator->validated();

        return $validator->passes();
    }

    public function message(): string
    {
        return '';
    }

    public function sometimes(): self
    {
        $this->sometimes = true;

        return $this;
    }

    public function docs(): array
    {
        return [
            'type'   => 'object',
            'setter' => (fn () => ['en' => Str::random()]),
        ];
    }
}
