<?php

namespace MetaFox\Ban\Supports;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Ban\Contracts\SupportInterface;
use MetaFox\Ban\Contracts\TypeHandlerInterface;
use MetaFox\Ban\Models\BanRule;
use MetaFox\Ban\Repositories\Eloquent\BanRuleRepository;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Exceptions\ValidateUserException;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class Support implements SupportInterface
{
    public function __construct(protected BanRuleRepository $banRuleRepository) {}

    public function getAllowedBanRuleTypes(): array
    {
        return [Constants::BAN_EMAIL_TYPE, Constants::BAN_IP_ADDRESS_TYPE, Constants::BAN_WORD_TYPE];
    }

    public function getValidationRules(string $type): array
    {
        return $this->resolveTypeHandler($type)->getValidationRules();
    }

    public function getValidatedRules(string $type, array $dataCheck): array
    {
        return $this->resolveTypeHandler($type)->getValidatedRules($dataCheck);
    }

    public function resolveTypeHandler(string $type): TypeHandlerInterface
    {
        $handler = LoadReduce::get(sprintf('filter::resolveBanRuleTypeHandler(%s)', $type), function () use ($type) {
            /**
             * @var string|null $driver
             */
            $driver = resolve(DriverRepositoryInterface::class)
                ->getDriver(Constants::BAN_RULE_TYPE_DRIVER_TYPE, $type, MetaFoxConstant::RESOLUTION_WEB);

            if (null === $driver) {
                return null;
            }

            if (!class_exists($driver)) {
                return null;
            }

            return resolve($driver);
        });

        if (!$handler instanceof TypeHandlerInterface) {
            throw new InvalidArgumentException("Handler for type '{$type}' not found or invalid.");
        }

        return $handler;
    }

    public function isSupportBanUser(string $type): bool
    {
        return $this->resolveTypeHandler($type)->isSupportBanUser();
    }

    public function automaticBan(?User $user, mixed $value): void
    {
        if (!$this->isValidBanInput($user, $value)) {
            return;
        }

        $rules = $this->banRuleRepository->getBanRulesByType(Constants::BAN_WORD_TYPE);

        /** @var BanRule $rule */
        foreach ($rules as $rule) {
            if ($this->shouldSkipBanRule($user, $rule)) {
                continue;
            }

            if (!$this->isValueBanned($rule, $value)) {
                continue;
            }

            $this->processBanUser($user, $rule);
        }
    }

    protected function processBanUser(User $user, BanRule $rule): void
    {
        $reason = $rule->reason ?: __p('ban::phrase.you_are_banned_because_you_used_banned_word', ['word' => $rule->find_value]);

        resolve(UserRepositoryInterface::class)
            ->banUser($rule->user, $user, $rule->day_banned, $rule->return_user_group, $reason);

        Artisan::call('cache:reset');

        throw new ValidateUserException([
            'title'   => __p('core::phrase.oops'),
            'message' => $reason,
        ], 403);
    }

    protected function isValueBanned(BanRule $rule, string $value): bool
    {
        $findValue = $this->prepareFilter($rule->find_value);

        if (preg_match('/\*/i', $findValue)) {
            $findValue = str_replace(['.', '*'], ['\.', '(.*?)'], $findValue);

            return preg_match('/' . $findValue . '/is', $value);
        }

        if (preg_match("/([^\p{L}\p{M}\p{N}])" . $findValue . "([^\p{L}\p{M}\p{N}])/i", $value)) {
            return true;
        }

        if (preg_match('/^' . $findValue . "([^\p{L}\p{M}\p{N}])/i", $value)) {
            return true;
        }

        if (preg_match("/([^\p{L}\p{M}\p{N}])" . $findValue . '$/i', $value)) {
            return true;
        }

        if (preg_match('/^' . $findValue . '$/i', $value)) {
            return true;
        }

        return false;
    }

    protected function prepareFilter(string $findValue): string
    {
        $findValue = str_replace('/', "\/", $findValue);

        return str_replace('&#42;', '*', $findValue);
    }

    protected function isValidBanInput(?User $user, mixed $value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (!$this->isValidUser($user)) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return false;
        }

        return true;
    }

    protected function shouldSkipBanRule(User $user, BanRule $rule): bool
    {
        if (empty($rule->day_banned)) {
            return true;
        }

        if (empty($rule->user_group_effected)) {
            return true;
        }

        if (!is_array($rule->user_group_effected)) {
            return true;
        }

        if (!in_array($user->roleId(), $rule->user_group_effected)) {
            return true;
        }

        return false;
    }

    public function validate(string $type, mixed $value): bool
    {
        if (!is_string($value) || $value === MetaFoxConstant::EMPTY_STRING) {
            return true;
        }

        $validationResult = $this->performValidation($type, $value);

        return (bool) Arr::get($validationResult, 'is_valid');
    }

    public function validateMultipleType(?User $user = null): void
    {
        if ($this->isValidUser($user) && $user->hasSuperAdminRole()) {
            return;
        }

        if ($this->isValidUser($user)) {
            $this->validateUserFields($user);
        }

        $this->validateUserIPAddress($user);
    }

    protected function validateUserFields(User $user): void
    {
        foreach ($this->mappingTypeWithUserField() as $type => $field) {
            $fieldValue = $user->{$field};

            if (empty($fieldValue)) {
                continue;
            }

            if ($this->validate($type, $fieldValue)) {
                continue;
            }

            $this->revokeUserAccess($user);

            throw new ValidateUserException([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('user::phrase.global_ban_message'),
            ], 403);
        }
    }

    protected function isValidUser(?User $user): bool
    {
        return $user instanceof User && !$user->isGuest();
    }

    protected function validateUserIPAddress(?User $user): void
    {
        if ($this->validateIPAddress(request()->ip())) {
            return;
        }

        if ($this->isValidUser($user)) {
            $this->revokeUserAccess($user);
        }

        throw new ValidateUserException([
            'title'   => __p('core::phrase.oops'),
            'message' => __p('user::phrase.not_allowed_ip_address'),
        ], 427);
    }

    protected function revokeUserAccess(User $user): void
    {
        $user->revokeAllTokens();

        resolve(DeviceRepositoryInterface::class)->logoutAllByUser($user);

        Artisan::call('cache:reset');
    }

    protected function mappingTypeWithUserField(): array
    {
        return [
            Constants::BAN_EMAIL_TYPE => Constants::USER_EMAIL_FIELD,
        ];
    }

    public function validateEmail(mixed $value): bool
    {
        return $this->validate(Constants::BAN_EMAIL_TYPE, $value);
    }

    public function validateWord(mixed $value): bool
    {
        return $this->validate(Constants::BAN_WORD_TYPE, $value);
    }

    public function validateIPAddress(mixed $value): bool
    {
        return $this->validate(Constants::BAN_IP_ADDRESS_TYPE, $value);
    }

    public function validateWithReturnReason(string $type, mixed $value): array
    {
        return $this->performValidation($type, $value);
    }

    protected function performValidation(string $type, mixed $value): array
    {
        return $this->resolveTypeHandler($type)->validate($type, $value);
    }
}
