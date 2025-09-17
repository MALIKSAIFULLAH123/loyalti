<?php

namespace MetaFox\Platform\Traits\ApiDoc;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasCustomValidationRules
{
    /**
     * @param mixed $rule
     * @param array $parameterData
     * @param bool $independentOnly
     * @param array $allParameters
     *
     * @return bool
     */
    protected function parseRule($rule, array &$parameterData, bool $independentOnly, array $allParameters = []): bool
    {
        if ($this->parseCustomRule($rule, $parameterData)) {
            return true;
        }

        $result = parent::parseRule($rule, $parameterData, $independentOnly, $allParameters);

        return $result;
    }

    /**
     * Generate custom rule based on matched rule name.
     * @param mixed $rule
     * @param array $parameterData
     * @param bool $independentOnly
     * @param array $allParameters
     *
     * @return bool
     */
    protected function parseCustomRule($rule, array &$parameterData): bool
    {
        if (!is_string($rule)) {
            return false;
        }

        $name = Arr::get($parameterData, 'name');
        [$type, $arguments] = $this->parseStringRuleIntoRuleAndArguments($rule);

        switch ($type) {
            case 'string':
            case 'numeric':
                if (Str::endsWith($name, '_id') || in_array($name, ['id', 'limit', 'page'])) {
                    return $this->handleIntRule($rule, $parameterData);
                }

                if (Str::endsWith($name, '_date')) {
                    return $this->handleDateRule($rule, $parameterData);
                }

                if (Str::startsWith($name, 'is_')) {
                    return $this->generateAllowInRule($rule, $parameterData, [0, 1]);
                }

                if ($name == 'level') {
                    return $this->generateAllowInRule($rule, $parameterData, [1, 2, 3]);
                }

                if ($name == 'phone_number') {
                    return $this->handlePhoneNumberRule($rule, $parameterData);
                }

                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * Generate custom rule for integer value.
     * @param mixed $rule
     * @param array $parameterData
     *
     * @return bool
     */
    protected function handleIntRule($rule, array &$parameterData): bool
    {
        $parameterData['type'] = 'integer';
        $parameterData['setter'] = fn() => $this->generateDummyValue('integer');

        return true;
    }

    /**
     * Generate custom rule for date value.
     * @param mixed $rule
     * @param array $parameterData
     *
     * @return bool
     */
    protected function handleDateRule($rule, array &$parameterData): bool
    {
        $parameterData['type'] = 'string';
        $parameterData['description'] .= ' ' . $this->getDescription($rule);
        $parameterData['setter'] = fn() => date('Y-m-d\TH:i:s', time());

        return true;
    }

    /**
     * Generate custom rule for allowable set of values.
     * @param mixed $rule
     * @param array $parameterData
     * @param array $allows
     *
     * @return bool
     */
    protected function generateAllowInRule($rule, array &$parameterData, array $allows = []): bool
    {
        $parameterData['description'] = ' ' . sprintf('Must be one of <code>%s</code>', implode('</code>, <code>', $allows));
        $parameterData['setter'] = (fn() => Arr::random($allows));

        return true;
    }

    /**
     * Generate custom phone number rule.
     *
     * @param mixed $rule
     * @param array $parameterData
     *
     * @return bool
     */
    protected function handlePhoneNumberRule($rule, array &$parameterData): bool
    {
        $parameterData['type'] = 'string';
        $parameterData['description'] .= ' ' . 'Must be a valid phone number';
        $parameterData['setter'] = fn() => '+1408123456';

        return true;
    }
}
