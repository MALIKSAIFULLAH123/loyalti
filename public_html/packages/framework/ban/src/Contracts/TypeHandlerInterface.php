<?php

namespace MetaFox\Ban\Contracts;

interface TypeHandlerInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getFormTitle(): string;

    /**
     * @return array
     */
    public function getFilterFields(): array;

    /**
     * @return array
     */
    public function getValidationRules(): array;

    /**
     * @param array $data
     * @return array
     */
    public function getValidatedRules(array $data): array;

    /**
     * @param string $type
     * @param mixed  $value
     * @return array
     */
    public function validate(string $type, mixed $value): array;

    /**
     * @return bool
     */
    public function isSupportBanUser(): bool;

    /**
     * @return array
     */
    public function getBanUserFields(): array;
}
