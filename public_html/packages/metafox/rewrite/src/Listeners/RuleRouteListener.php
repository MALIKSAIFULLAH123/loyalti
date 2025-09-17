<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rewrite\Listeners;

use Illuminate\Support\Str;
use MetaFox\Rewrite\Models\Rule;

/**
 * Class UrlAliasRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class RuleRouteListener
{
    /**
     * @param string $url
     *
     * @return array<string,mixed>|void
     */
    public function handle(string $url)
    {
        if (Str::startsWith($url, 'admincp')) {

            /** @var Rule $alias */
            $alias = Rule::query()->where('from_path', '=', $url)->first();

            if ($alias) {
                return [
                    'from_path'      => $alias->from_path,
                    'to_path'        => $alias->to_path,
                    'to_mobile_path' => $alias->to_mobile_path,
                ];
            }
        }
    }
}
