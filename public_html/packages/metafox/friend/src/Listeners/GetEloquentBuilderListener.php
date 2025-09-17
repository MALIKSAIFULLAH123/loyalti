<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\Eloquent\FriendRepository;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;

class GetEloquentBuilderListener
{
    public function __construct()
    {

    }

    public function handle()
    {
        return resolve(FriendRepositoryInterface::class)->getModel()->newQuery();
    }
}
