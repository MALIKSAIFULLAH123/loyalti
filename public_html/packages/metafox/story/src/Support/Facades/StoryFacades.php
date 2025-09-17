<?php

namespace MetaFox\Story\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Story\Contracts\StoryContract;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Support\StorySupport;

/**
 * Class StoryFacades.
 * @method static int                   setExpired(int $lifespan = StorySupport::USER_STORY_LIFESPAN)
 * @method static bool                  hasSeen(User $context, Story $story)
 * @method static bool                  hasLive(User $context, Story $story)
 * @method static array|null            getReactionByUser(User $context, Story $story)
 * @method static Collection            getStories(User $context, StorySet $storySet)
 * @method static Collection            viewServices(User $context, array $params = [])
 * @method static VideoServiceInterface getDefaultServiceClass()
 * @method static VideoServiceInterface getVideoServiceClassByDriver(string $driver)
 * @method static array                 getServicesOptions()
 * @method static array                 allowTypeViewStory()
 * @method static array                 getFontStyleOptions()
 * @method static array                 getPrivacyOptions()
 * @method static array                 getLifespanOptions()
 * @method static array                 getVideoDurationOptions()
 * @method static bool                  checkReadyService()
 * @method static int                   getConfiguredVideoDuration()
 * @method static int                   getLifespanDefault()
 * @method static array                 getMutedOptions()
 * @method static string|null           parseMuteExpiredAt(string $value)
 * @method static bool                  isMuted(User $context, int $ownerId)
 */
class StoryFacades extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StoryContract::class;
    }
}
