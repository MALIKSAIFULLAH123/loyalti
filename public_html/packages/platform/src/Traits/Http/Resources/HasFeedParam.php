<?php

namespace MetaFox\Platform\Traits\Http\Resources;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Http\Resources\v1\FeedParam\FeedParamDetail;

/**
 * Trait HasFeedParam.
 * @property Content $resource
 */
trait HasFeedParam
{
    /**
     * @return FeedParamDetail
     */
    protected function getFeedParams(): FeedParamDetail
    {
        return new FeedParamDetail($this->resource);
    }
}
