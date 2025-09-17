<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Policy;

/**
 * Class PolicyManager.
 */
class RulePolicy
{
    /** @var array<string,string> */
    private array $rules = [];

    public function addRule(string $model, string $handler): void
    {
        $this->rules[$model] = $handler;
    }

    /**
     * @param string $ability
     *
     * @return mixed
     */
    public function getRuleFor(string $ability)
    {
        if (!isset($this->rules[$ability])) {
            return null;
        }

        return app($this->rules[$ability]);
    }

    /**
     * @param string       $type
     * @param string       $ability
     * @param array<mixed> $arguments
     *
     * @return bool
     */
    public function check(string $type, string $ability, array $arguments): bool
    {
        if (!isset($this->rules[$ability])) {
            return true;
        }

        $handler = $this->getRuleFor($ability);

        /** @var mixed $args */
        $args = [$handler, 'check'];

        if (!is_callable($args)) {
            return false;
        }

        return call_user_func($args, $type, ...$arguments);
    }
}
