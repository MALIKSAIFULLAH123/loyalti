<?php

namespace MetaFox\Comment\Http\Resources\v1\Pending\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Traits\HasTransformContent;

/**
 * Class PendingDetail.
 * @property Comment $resource
 */
class PendingDetail extends PendingItem
{
}
