<?php

namespace MetaFox\User\Http\Resources\v1\CancelFeedback\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CancelFeedbackItemCollection extends ResourceCollection
{
    public $collects = CancelFeedbackItem::class;
}
