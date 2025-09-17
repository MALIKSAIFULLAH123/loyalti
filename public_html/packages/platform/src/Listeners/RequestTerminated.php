<?php

namespace MetaFox\Platform\Listeners;

use MetaFox\Platform\Facades\RequestLifecycle;

class RequestTerminated
{
    public function handle()
    {
        RequestLifecycle::handleTerminated();
    }
}
