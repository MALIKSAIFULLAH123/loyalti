<?php

namespace MetaFox\Captcha\Support\Contracts;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Section;

interface CaptchaSupportContract
{
    /**
     * @param  string      $token
     * @param  string|null $action
     * @return bool
     */
    public function verify(string $token, ?string $action = null): bool;

    /**
     * @param  string|null $action
     * @return array
     */
    public function ruleOf(?string $action = null): array;

    /**
     * @param  string|null $action
     * @return string|null
     */
    public function errorMessage(?string $action = null): ?string;

    /**
     * @param  string|null $type
     * @return void
     */
    public function resolveCaptcha(?string $type = null): void;

    /**
     * @param  string|null        $action
     * @param  string             $resolution
     * @param  bool               $isPreload
     * @param  string             $fieldName
     * @param  bool               $isHidden
     * @return AbstractField|null
     */
    public function getFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha', bool $isHidden = true): ?AbstractField;

    /**
     * @return string
     */
    public function getDefaultCaptchaType(): string;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param  Section     $basic
     * @param  string|null $action
     * @param  string      $resolution
     * @param  bool        $isPreload
     * @param  string      $fieldName
     * @return Section
     */
    public function addFormField(Section $basic, ?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha'): Section;

    /**
     * @param  string|null $action
     * @return array
     */
    public function refresh(?string $action = null): array;

    /**
     * @param  string|null $action
     * @param  string      $name
     * @return array|null
     */
    public function ruleMessage(?string $action = null, string $name = 'captcha'): ?array;

    /**
     * @return array
     */
    public function getRules(): array;
}
