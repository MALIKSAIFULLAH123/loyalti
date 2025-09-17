<?php

namespace MetaFox\Follow\Http\Resources\v1\Follow;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * |--------------------------------------------------------------------------
 * | Resource Pattern
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/item_collection.stub
 */

/**
 * Class FollowItemCollection.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class FollowItemCollection extends ResourceCollection
{
    protected array $extraMeta = [];
    public          $collects  = FollowItem::class;

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setExtraMeta(array $attributes): self
    {
        $this->extraMeta = $attributes;

        return $this;
    }

    /**
     * @param Request $request
     * @param array   $paginated
     * @param array   $default
     *
     * @return array
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $meta = Arr::get($default, 'meta');

        $meta = array_merge($meta, $this->extraMeta);

        Arr::set($default, 'meta', $meta);

        return $default;
    }
}
