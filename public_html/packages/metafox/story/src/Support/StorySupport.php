<?php

namespace MetaFox\Story\Support;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Story\Contracts\StoryContract;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryReaction;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Repositories\MuteRepositoryInterface;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StoryViewRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;

class StorySupport implements StoryContract
{
    public const RESIZE_IMAGE                     = ['150'];
    public const STORY_TYPE_PHOTO                 = 'photo';
    public const STORY_TYPE_TEXT                  = 'text';
    public const STORY_TYPE_SHARE                 = 'share';
    public const STORY_TYPE_VIDEO                 = 'video';
    public const STORY_TYPE_LIVE_VIDEO            = 'live_video';
    public const DISPLAY_THE_USER_AVATAR          = '1';
    public const DISPLAY_THE_THUMBNAIL_AND_AVATAR = '2';
    public const USER_STORY_LIFESPAN              = 24;
    public const STORY_AUTO_ARCHIVE               = 1;
    public const STORY_DURATION_DEFAULT           = 10;
    public const STORY_VIDEO_DURATION_DEFAULT     = 30;
    public const FONT_STYLE_DEFAULT               = 'Roboto';
    public const STATUS_VIDEO_READY               = 0;
    public const STATUS_VIDEO_PROCESS             = 1;
    public const STATUS_VIDEO_FAILED              = 2;
    public const MUTED_ONE_DAY                    = 'one_day';
    public const MUTED_ONE_WEEK                   = 'one_week';
    public const MUTED_FOREVER                    = 'forever';
    public const LIFESPAN_VALUE_OPTIONS           = [6, 12, 24, 48, 72];

    protected function repository(): StoryRepositoryInterface
    {
        return resolve(StoryRepositoryInterface::class);
    }

    protected function driverRepository(): DriverRepositoryInterface
    {
        return resolve(DriverRepositoryInterface::class);
    }

    protected function viewRepository(): StoryViewRepositoryInterface
    {
        return resolve(StoryViewRepositoryInterface::class);
    }

    protected function reactionRepository(): StoryReactionRepositoryInterface
    {
        return resolve(StoryReactionRepositoryInterface::class);
    }

    protected function muteRepository(): MuteRepositoryInterface
    {
        return resolve(MuteRepositoryInterface::class);
    }

    public function setExpired(int $lifespan = self::USER_STORY_LIFESPAN): int
    {
        return Carbon::now()->addHours($lifespan)->timestamp;
    }

    /**
     * @inheritDoc
     */
    public function hasSeen(User $context, Story $story): bool
    {
        return $this->viewRepository()->hasSeenStory($context, $story->entityId());
    }

    public function hasLive(User $context, Story $story): bool
    {
        return $story->extra && Arr::get($story->extra, 'is_streaming', false);
    }

    /**
     * @inheritDoc
     */
    public function getReactionByUser(User $context, Story $story): ?array
    {
        $reaction = $this->reactionRepository()->getReaction($context, $story);
        if (!$reaction instanceof StoryReaction) {
            return null;
        }

        return ResourceGate::item($reaction, false);
    }

    /**
     * @inheritDoc
     */
    public function getStories(User $context, StorySet $storySet): Collection
    {
        $stories = $this->repository()->getStories($context, $storySet);

        return $stories;
    }

    /**
     * @inheritDoc
     */
    public function viewServices(User $context, array $params = []): Collection
    {
        return $this->driverRepository()->getDrivers(MetaFoxConstant::STORY_SERVICE_TYPE, null, MetaFoxConstant::RESOLUTION_ADMIN);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultServiceClass(): VideoServiceInterface
    {
        $defaultService = Settings::get('story.video_service');

        return $this->getVideoServiceClassByDriver($defaultService);
    }

    /**
     * @inheritDoc
     */
    public function getServicesOptions(): array
    {
        return $this->driverRepository()->getModel()
            ->newModelQuery()
            ->where('is_active', '=', 1)
            ->where('type', MetaFoxConstant::STORY_SERVICE_TYPE)
            ->get()
            ->collect()
            ->map(function ($service) {
                return [
                    'label' => __p($service->title),
                    'value' => $service->name,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getVideoServiceClassByDriver(string $driver): VideoServiceInterface
    {
        [, $serviceClass] = $this->driverRepository()->loadDriver(
            MetaFoxConstant::STORY_SERVICE_TYPE,
            $driver,
            MetaFoxConstant::RESOLUTION_ADMIN
        );

        $serviceClass = match ($driver) {
            'ffmpeg' => new $serviceClass([
                'item_type' => Story::ENTITY_TYPE,
            ]),
            default  => new $serviceClass(Story::ENTITY_TYPE),
        };

        if (!$serviceClass instanceof VideoServiceInterface) {
            abort(400, __p('video::phrase.no_active_video_service'));
        }

        return $serviceClass;
    }

    public function allowTypeViewStory(): array
    {
        $typeDefault = [
            self::STORY_TYPE_TEXT,
            self::STORY_TYPE_PHOTO,
        ];

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.7', '<')) {
            return $typeDefault;
        }

        return array_merge($typeDefault, [self::STORY_TYPE_VIDEO, self::STORY_TYPE_LIVE_VIDEO]);
    }

    /**
     * @inheritDoc
     */
    public function getFontStyleOptions(): array
    {
        return [
            [
                'value' => 'Roboto',
                'label' => __p('story::phrase.font.nomal'),
            ],
            [
                'value' => 'Nunito',
                'label' => __p('story::phrase.font.slight'),
            ],
            [
                'value' => 'Playfair Display',
                'label' => __p('story::phrase.font.soft'),
            ],
            [
                'value' => 'Pacifico',
                'label' => __p('story::phrase.font.mightype'),
            ],
            [
                'value' => 'Rubik Doodle Shadow',
                'label' => __p('story::phrase.font.shadow'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPrivacyOptions(): array
    {
        $phrase = MetaFoxPrivacy::getPrivacy();

        $results = [
            [
                'value' => MetaFoxPrivacy::MEMBERS,
                'label' => __p($phrase[MetaFoxPrivacy::MEMBERS]),
            ], [
                'value' => MetaFoxPrivacy::FRIENDS,
                'label' => __p($phrase[MetaFoxPrivacy::FRIENDS]),
            ], [
                'value' => MetaFoxPrivacy::CUSTOM,
                'label' => __p($phrase[MetaFoxPrivacy::CUSTOM]),
            ],
        ];

        $user = Auth::user() ?? UserFacade::getGuestUser();
        if ($user->isGuest()) {
            return $results;
        }

        $allowPrivacy = [];
        app('events')->dispatch('story.support.override_privacy_option', [$user, &$allowPrivacy]);

        if (empty($allowPrivacy)) {
            return $results;
        }

        $results = collect($results)->whereIn('value', $allowPrivacy)->toArray();

        return array_values($results);
    }

    public function getLifespanOptions(): array
    {
        $options  = [];
        $settings = Settings::get('story.lifespan_options', self::LIFESPAN_VALUE_OPTIONS);

        foreach ($settings as $value) {
            $options[] = [
                'value' => $value,
                'label' => __p('story::phrase.lifespan_number_hour', ['number' => $value]),
            ];
        }

        return array_values(Arr::sort($options, fn (array $ar) => $ar['value']));
    }

    public function checkReadyService(): bool
    {
        return Cache::rememberForever('story_check_ready_video', function () {
            try {
                $service = $this->getDefaultServiceClass();

                if (method_exists($service, 'testConfig')) {
                    return $service->testConfig();
                }

                return true;
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    public function getConfiguredVideoDuration(): int
    {
        return Settings::get('story.duration_video_story', self::STORY_VIDEO_DURATION_DEFAULT);
    }

    public function getVideoDurationOptions(): array
    {
        return [
            [
                'value' => 30,
                'label' => ucwords(CarbonInterval::make(30 . 's')->forHumans()),
            ],
            [
                'value' => 60,
                'label' => ucwords(CarbonInterval::make(60 . 's')->forHumans()),
            ],
            [
                'value' => 90,
                'label' => ucwords(CarbonInterval::make(90 . 's')->forHumans()),
            ],
        ];
    }

    public function getMutedOptions(): array
    {
        return [
            [
                'label' => __p("story::phrase.muted." . self::MUTED_ONE_DAY),
                'value' => self::MUTED_ONE_DAY,
            ],
            [
                'label' => __p("story::phrase.muted." . self::MUTED_ONE_WEEK),
                'value' => self::MUTED_ONE_WEEK,
            ],
            [
                'label' => __p("story::phrase.muted." . self::MUTED_FOREVER),
                'value' => self::MUTED_FOREVER,
            ],
        ];
    }

    public function parseMuteExpiredAt(string $value): ?string
    {
        return match ($value) {
            self::MUTED_ONE_DAY  => Carbon::now()->addDay()->timestamp,
            self::MUTED_ONE_WEEK => Carbon::now()->addWeek()->timestamp,
            self::MUTED_FOREVER  => null,
        };
    }

    public function isMuted(User $context, int $ownerId): bool
    {
        return $this->muteRepository()->isMuted($context, $ownerId);
    }

    public function getLifespanDefault(): int
    {
        return Settings::get('story.lifespan_default', self::USER_STORY_LIFESPAN);
    }
}
