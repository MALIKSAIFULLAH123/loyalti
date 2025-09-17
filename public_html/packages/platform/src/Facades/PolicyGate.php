<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Traits\Policy\PolicyManager;

/**
 * Class PolicyGate.
 * @method static bool check(string $type, string $ability, ...$arguments)
 * @method static mixed getPolicyFor(string $model)
 * @method static null|string getModelFor(string $policy)
 * @method static void addPolicy(string $model, string $policy)
 * @method static void addRule(string $model, string $handler)
 * @method static array getRules()
 * @method static array getPolicies()
 * @link PolicyManager
 */
class PolicyGate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PolicyManager::class;
    }
}
