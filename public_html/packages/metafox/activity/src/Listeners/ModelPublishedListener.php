<?php

namespace MetaFox\Activity\Listeners;

class ModelPublishedListener extends ModelCreatedListener
{
    public function handle($model): void
    {
        parent::handle($model);
    }
}
