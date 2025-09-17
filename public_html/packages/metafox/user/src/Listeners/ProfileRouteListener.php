<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\UserEntity;

/**
 * Class ProfileRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ProfileRouteListener
{
    /**
     * @param string $url
     *
     * @return array<string,mixed>|null|void
     */
    public function handle(string $url)
    {
        try {
            $parts       = parse_url($url);
            $path        = Arr::get($parts, 'path', MetaFoxConstant::EMPTY_STRING);
            $queryString = Arr::get($parts, 'query');
            $array       = explode('/', $path);
            $name        = array_shift($array);

            /**
             * @todo improve later
             * @var UserEntity $user
             */
            $user = UserEntity::query()
                ->whereRaw('lower(user_name)=lower(?)', [$name])
                ->firstOrFail();

            $entityId = $user->entityId();
            $prefix   = $user->entityType();

            array_unshift($array, $entityId);
            array_unshift($array, $prefix);

            $path = '/' . implode('/', $array);

            return [
                'path' => $queryString ? $path . "?$queryString" : $path,
            ];
        } catch (\Exception $exception) {
            // do nothing
        }
    }
}
