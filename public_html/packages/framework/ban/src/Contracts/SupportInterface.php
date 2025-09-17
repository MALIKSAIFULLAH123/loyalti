<?php

namespace MetaFox\Ban\Contracts;

use MetaFox\User\Models\User;

interface SupportInterface
{
    /**
     * @return array
     */
    public function getAllowedBanRuleTypes(): array;

    /**
     * @param string $type
     * @return bool
     */
    public function isSupportBanUser(string $type): bool;

    /**
     * @param User|null $user
     * @param mixed     $value
     * @return void
     */
    public function automaticBan(?User $user, mixed $value): void;

    /**
     * @param string $type
     * @return array
     */
    public function getValidationRules(string $type): array;

    /**
     * @param string $type
     * @param array  $dataCheck
     * @return array
     */
    public function getValidatedRules(string $type, array $dataCheck): array;

    /**
     * @param string $type
     * @return TypeHandlerInterface
     */
    public function resolveTypeHandler(string $type): TypeHandlerInterface;

    /**
     * @param string $type
     * @param mixed  $value
     * @return bool
     */
    public function validate(string $type, mixed $value): bool;

    /**
     * @param User|null $user
     * @return void
     */
    public function validateMultipleType(?User $user = null): void;

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateEmail(mixed $value): bool;

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateIPAddress(mixed $value): bool;

    /**
     * @param string $type
     * @param mixed  $value
     * @return array
     */
    public function validateWithReturnReason(string $type, mixed $value): array;
}
