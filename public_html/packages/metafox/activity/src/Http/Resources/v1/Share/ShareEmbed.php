<?php

namespace MetaFox\Activity\Http\Resources\v1\Share;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use MetaFox\Activity\Models\Share as Model;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class ShareEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ShareEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $user    = user();
        $service = resolve(FeedRepositoryInterface::class);

        $shareEmbed = null;
        try {
            $item = $this->resource->item;
            // @todo check resource is deleted ??
            if (!$item instanceof Entity) {
                return [];
            }

            switch ($this->resource->parent_feed_id) {
                case 0:
                    $shareEmbed = $item;
                    break;
                default:
                    $shareEmbed = $service->getFeed($user, $this->resource->parent_feed_id);
            }
        } catch (\Exception $e) {
            Log::error($e);
            // Silent.
        }

        if ($shareEmbed === null) {
            return [];
        }

        $embed = ResourceGate::asResource($shareEmbed, 'feed_embed');

        /*
         * Fallback in case apps have not upgraded yet
         */
        if (null === $embed) {
            $embed = ResourceGate::asEmbed($shareEmbed);
        }

        if (!$embed instanceof JsonResource) {
            return [];
        }

        return $embed->toArray($request);
    }
}
