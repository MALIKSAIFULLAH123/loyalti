<?php

namespace MetaFox\Forum\Support\Browse\Traits\ForumPost;

use MetaFox\Forum\Support\Facades\ForumPost as Facade;

trait ExtraTrait
{
    public function getPostExtra(): array
    {
        $resource = $this->resource;

        $context = user();

        return Facade::getCustomExtra($context, $resource);
    }
}
