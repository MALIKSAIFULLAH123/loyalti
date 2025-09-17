<?php

namespace MetaFox\Ban\TypeHandlers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Ban\Rules\UniqueBanRuleRule;
use MetaFox\Ban\Supports\Constants;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\IpRule;
use MetaFox\Yup\Yup;

class IPAddressHandler extends AbstractTypeHandler
{
    public function getType(): string
    {
        return Constants::BAN_IP_ADDRESS_TYPE;
    }

    public function getValidationRules(): array
    {
        return [
            'find_value' => ['required', new IpRule(), new UniqueBanRuleRule()],
        ];
    }

    public function getFormTitle(): string
    {
        return __p('ban::phrase.add_new_ip_address');
    }

    public function getFilterFields(): array
    {
        return [
            Builder::text('find_value')
                ->required()
                ->label(__p('ban::phrase.ip_address'))
                ->description(__p('ban::phrase.use_the_asterisk_for_wildcard_ip_entries'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable()
                        ->matches(MetaFox::getWildCardIpAddressRegex(), __p('ban::phrase.invalid_ip_address_format'))
                ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getValidatedRules(array $data): array
    {
        $dataCheck      = $data;
        $regexIpPattern = MetaFox::getWildCardIpAddressRegex();
        $rules          = [
            'find_value' => ['required', 'string', function ($attribute, $value, $fail) use ($regexIpPattern) {
                if (preg_match("/$regexIpPattern/", $value)) {
                    $fail(__p('regex::phrase.name_must_not_allow_unicode', ['name' => __p('regex::phrase.username_regex_rule')]));
                }
            },
            ],
        ];

        $value = Arr::get($dataCheck, 'find_value', '');

        Arr::set($dataCheck, 'find_value', $this->processNormalizeFindValue($value));

        $validator = Validator::make($dataCheck, $rules);
        $validator->validate();

        return $data;
    }
}
