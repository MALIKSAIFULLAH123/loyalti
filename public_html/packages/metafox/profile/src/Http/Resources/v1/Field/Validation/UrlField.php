<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Validation;

use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Rules\UrlRule;
use MetaFox\Profile\Support\Validation\ValidationFieldRule;
use MetaFox\Profile\Support\Validation\ValidationFieldRuleInterface;
use MetaFox\Yup\MixedShape;
use MetaFox\Yup\Yup;

/**
 * @driverName validation.url
 * @driverType custom-field-validator
 * @resolution admin
 */
class UrlField extends ValidationFieldRule implements ValidationFieldRuleInterface
{
    public const NOT_APPLIED     = 'not_apply';
    public const TYPE_ALLOWED    = 'allowed';
    public const TYPE_DISALLOWED = 'disallowed';

    public function getFields(): void
    {
        $this->section->addFields(
            Builder::choice('url_rule_type')
                ->label(__p('profile::phrase.domains_filter'))
                ->showWhen($this->getShowWhen())
                ->disableClearable()
                ->returnKeyType('next')
                ->options([
                    [
                        'value'       => self::NOT_APPLIED,
                        'label'       => __p('profile::phrase.not_applied'),
                        'description' => __p('profile::phrase.not_applied_description'),
                    ],
                    [
                        'value'       => self::TYPE_ALLOWED,
                        'label'       => __p('profile::phrase.allowed_domains'),
                        'description' => __p('profile::phrase.allowed_domains_description'),
                    ],
                    [
                        'value'       => self::TYPE_DISALLOWED,
                        'label'       => __p('profile::phrase.disallowed_domains'),
                        'description' => __p('profile::phrase.disallowed_domains_description'),
                    ],
                ]),
            Builder::tags('url_rule_values')
                ->label(__p('profile::phrase.domains'))
                ->description(__p('profile::phrase.please_separate_multiple_domains_by_using_the_enter_key'))
                ->showWhen($this->getShowWhenByParent())
                ->disableSuggestion()
                ->yup(Yup::array()
                    ->nullable()
                    ->of(Yup::string()
                        ->nullable()
                        ->matches($this->patternDomain(), __p('profile::validation.this_field_must_be_a_valid_domain')))),
        );
    }

    public function getShowWhenByParent(): array
    {
        return [
            'and',
            $this->getShowWhen(),
            ['includes', 'url_rule_type', [self::TYPE_ALLOWED, self::TYPE_DISALLOWED]],
        ];
    }

    /**
     * @return array
     */
    public function appliesEditingComponent(): array
    {
        return [CustomField::URL];
    }

    /**
     * @return array[]
     */
    public function configRules(): array
    {
        return [
            'url_rule_type'     => ['sometimes', 'string'],
            'url_rule_values'   => ['sometimes', 'array'],
            'url_rule_values.*' => ['sometimes', 'regex:' . $this->patternDomainForRule()],
        ];
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function configValidated($data): array
    {
        $result = [];
        foreach ($this->fieldsName() as $item) {
            $value = Arr::get($data, $item);
            Arr::set($result, $item, $value ?? []);
        }

        return $result;
    }

    public function configMessagesRule(): array
    {
        $messagesAllow  = __p('profile::validation.this_field_must_be_a_valid_domain');
        $allowedDomains = request()->get('url_rule_values', []);

        if (!empty($allowedDomains)) {
            foreach ($allowedDomains as $item) {
                if (!preg_match($this->patternDomainForRule(), $item)) {
                    $messagesAllow = __p('profile::validation.attribute_must_be_a_valid_domain', [
                        'attribute' => $item,
                    ]);
                    break;
                }
            }
        }

        return [
            'url_rule_values.*.regex' => $messagesAllow,
        ];
    }

    /**
     * @return string[]
     */
    public function fieldsName(): array
    {
        return ['url_rule_type', 'url_rule_values'];
    }

    /**
     * @return array
     * Establish the relationship between the label field, message, and field names.
     */
    public function fieldsErrorMessageLabel(): array
    {
        return [
            'url_rule_type' => __p('profile::phrase.error_message'),
        ];
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    public function inputRules(array $rules): array
    {
        return ['url', new UrlRule($rules)];
    }

    /**
     * @param MixedShape $yup
     * @param Field      $field
     * @param array      $rules
     *
     * @return void
     */
    public function setYupFieldParent(MixedShape $yup, Field $field, array $rules): void
    {
        foreach ($rules as $key => $value) {
            if (!in_array($key, $this->fieldsName())) {
                continue;
            }

            $yup->url(__p('profile::validation.this_field_must_be_a_valid_domain'));
        }
    }

    /**
     * @param Field $field
     * @param array $data
     *
     * @return array
     */
    public function inputMessagesRule(Field $field, array $data): array
    {
        return [];
    }

    public function patternDomainForRule(): string
    {
        return sprintf('/%s/m', $this->patternDomain());
    }

    public function patternDomain(): string
    {
        return '^()+(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]+$';
    }

    public function getValuesDefault(): array
    {
        return [
            'url_rule_type' => self::NOT_APPLIED,
        ];
    }
}
