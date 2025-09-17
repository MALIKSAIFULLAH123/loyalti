<?php

namespace MetaFox\Captcha\Contracts;

use MetaFox\Form\AbstractField;

/**
 * Interface RecaptchaV3SupportContract.
 * @ignore
 */
interface CaptchaContract
{
    /**
     * @param  string      $token
     * @param  string|null $action
     * @return bool
     */
    public function verify(string $token, ?string $action = null, ?string $publicKey = null): bool;

    /**
     * @param  string|null $action
     * @return array
     */
    public function ruleOf(?string $action = null): array;

    /**
     * @return string
     */
    public function errorMessage(?string $action = null): string;

    /**
     * @param  string      $name
     * @param  string|null $action
     * @return array
     */
    public function ruleMessage(?string $action = null, string $name = 'captcha'): array;

    /**
     * @param  string|null        $action
     * @param  string             $resolution
     * @param  bool               $isPreload
     * @param  string             $fieldName
     * @param  bool               $isHidden
     * @return AbstractField|null
     */
    public function generateFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha', bool $isHidden = true): ?AbstractField;

    /**
     * @return array
     */
    public function refresh(): array;
}
