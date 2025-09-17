<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use Illuminate\Http\Request;
use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Support\Arr;

class SavedListItemCollection extends ResourceCollection
{
    /**
     * @var array
     */
    protected array $extraMeta = [];

    public $collects = SavedListItem::class;

    /**
     * @param  array $attributes
     * @return $this
     */
    public function setExtraMeta(array $attributes): self
    {
        $this->extraMeta = $attributes;

        return $this;
    }

    /**
     * @param  Request $request
     * @param  array   $paginated
     * @param  array   $default
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
