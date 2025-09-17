<?php

namespace MetaFox\Poll\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Poll\Contracts\PollSupportInterface;
use MetaFox\Poll\Models\Poll as PollModel;

/**
 * @method static int getIntegrationViewId()
 * @method static array getStatusTexts(PollModel $poll)
 */
class Poll extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PollSupportInterface::class;
    }
}
