<?php

namespace MetaFox\Ban\TypeHandlers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Ban\Contracts\TypeHandlerInterface;
use MetaFox\Ban\Models\BanRule;
use MetaFox\Ban\Repositories\Eloquent\BanRuleRepository;
use MetaFox\Ban\Rules\UniqueBanRuleRule;
use MetaFox\Form\Builder;
use MetaFox\Platform\Rules\RegexPatternRule;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;

abstract class AbstractTypeHandler implements TypeHandlerInterface
{
    public function __construct(protected BanRuleRepository $banRuleRepository) {}

    public function getValidationRules(): array
    {
        return [
            'find_value' => ['required', 'string', new UniqueBanRuleRule()],
        ];
    }

    public function isSupportBanUser(): bool
    {
        return false;
    }

    public function getBanUserFields(): array
    {
        return [
            Builder::switch('is_ban_user')
                ->label(__p('ban::phrase.ban_user_colon')),
            Builder::typography('reason_typo')
                ->showWhen(['truthy', 'is_ban_user'])
                ->variant('h5')
                ->plainText(__p('user::phrase.reason')),
            Builder::textArea('reason')
                ->showWhen(['truthy', 'is_ban_user'])
                ->returnKeyType('default')
                ->label(__p('user::phrase.reason')),
            Builder::typography('day_typo')
                ->showWhen(['truthy', 'is_ban_user'])
                ->variant('h5')
                ->plainText(__p('user::phrase.ban_for_how_many_days')),
            Builder::text('day')
                ->showWhen(['truthy', 'is_ban_user'])
                ->requiredWhen(['truthy', 'is_ban_user'])
                ->asNumber()
                ->label(__p('user::phrase.ban_for_how_many_days'))
                ->description(__p('user::phrase.0_means_indefinite'))
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->min(0)
                        ->unint()
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                        ->when(
                            Yup::when('is_ban_user')
                                ->is(1)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->min(0)
                                        ->unint()
                                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                                )
                        )
                ),
            Builder::typography('return_user_group_typo')
                ->showWhen(['truthy', 'is_ban_user'])
                ->variant('h5')
                ->plainText(__p('core::phrase.role')),
            Builder::choice('return_user_group')
                ->showWhen(['truthy', 'is_ban_user'])
                ->requiredWhen(['truthy', 'is_ban_user'])
                ->multiple(false)
                ->disableClearable()
                ->label(__p('core::phrase.role'))
                ->description(__p('user::phrase.role_to_move_the_user_when_the_ban_expires'))
                ->options($this->getRoleOptions())
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->positive()
                        ->when(
                            Yup::when('is_ban_user')
                                ->is(1)
                                ->then(
                                    Yup::number()
                                        ->nullable()
                                        ->positive()
                                        ->required()
                                )
                        )
                ),
            Builder::checkboxGroup('user_group_effected')
                ->showWhen(['truthy', 'is_ban_user'])
                ->requiredWhen(['truthy', 'is_ban_user'])
                ->label(__p('ban::phrase.roles_affected'))
                ->multiple()
                ->options($this->getRoleOptions())
                ->yup(Yup::array()->nullable()),
        ];
    }

    protected function getRoleOptions(): array
    {
        $roleOptions = array_filter(resolve(RoleRepositoryInterface::class)->getRoleOptions(), function ($role) {
            return Arr::get($role, 'value') != UserRole::SUPER_ADMIN_USER;
        });

        return array_values($roleOptions);
    }

    public function validate(string $type, mixed $value): array
    {
        $rules = $this->banRuleRepository->getBanRulesByType($type);

        /** @var BanRule $rule */
        foreach ($rules as $rule) {
            $findValue = $this->normalizeFindValue($rule);

            if (empty($findValue)) {
                continue;
            }

            if (!$this->matchesPattern($findValue, $value)) {
                continue;
            }

            return [
                'is_valid' => false,
                'reason'   => trim($rule->reason),
            ];
        }

        return [
            'is_valid' => true,
            'reason'   => null,
        ];
    }

    protected function normalizeFindValue(BanRule $rule): string|null
    {
        return Cache::remember(
            "ban::normalizeFindValue($rule->id, $rule->type_id, $rule->find_value)",
            3600,
            fn () => $this->processNormalizeFindValue($rule)
        );
    }

    protected function processNormalizeFindValue(BanRule|string $rule): ?string
    {
        $string    = $rule instanceof BanRule ? $rule->find_value : $rule;
        $findValue = $this->sanitizeFindValue($string);

        $findValue = $this->normalizeSpecificTypeValue($findValue);

        if ('*' == $findValue) {
            return null;
        }

        if (!$this->containsWildcard($findValue)) {
            return $findValue;
        }

        $findValue = $this->convertWildcards($findValue);

        if (!$this->containsProtocol($findValue)) {
            return $findValue;
        }

        return $this->escapeSlashes($findValue);
    }

    protected function sanitizeFindValue(string $findValue): string
    {
        return str_replace('&#42;', '*', trim($findValue));
    }

    protected function normalizeSpecificTypeValue(string $findValue): string
    {
        return $findValue;
    }

    protected function containsWildcard(string $findValue): bool
    {
        return preg_match('/(?|(\.)|(\*)|(\+))/i', $findValue);
    }

    protected function convertWildcards(string $findValue): string
    {
        return str_replace(['.', '*', '+'], ['\.', '(.*?)', '\+'], $findValue);
    }

    protected function containsProtocol(string $findValue): bool
    {
        return preg_match('/http(s?):\/\//i', $findValue);
    }

    protected function escapeSlashes(string $findValue): string
    {
        return str_replace('/', '\\/\\/', $findValue);
    }

    protected function matchesPattern(string $findValue, string $value): bool
    {
        return (bool) preg_match('/^' . $findValue . '$/i', $value);
    }

    /**
     * @inheritDoc
     */
    public function getValidatedRules(array $data): array
    {
        $dataCheck = $data;
        $rules     = ['find_value' => ['required', 'string', new RegexPatternRule()]];
        $value     = Arr::get($dataCheck, 'find_value', '');

        Arr::set($dataCheck, 'find_value', $this->processNormalizeFindValue($value));

        $validator = Validator::make($dataCheck, $rules);
        $validator->validate();

        return $data;
    }
}
