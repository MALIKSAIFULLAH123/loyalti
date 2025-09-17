<?php

namespace MetaFox\Core\Providers;

use Exception;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Core\Contracts\AttachmentFileTypeContract;
use MetaFox\Core\Contracts\EmojiSupportInterface;
use MetaFox\Core\Models\Attachment;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Core\Observers\AttachmentObserver;
use MetaFox\Core\Repositories\AdminAccessRepositoryInterface;
use MetaFox\Core\Repositories\AdminSearchRepositoryInterface;
use MetaFox\Core\Repositories\AppSettingRepository;
use MetaFox\Core\Repositories\AttachmentFileTypeRepositoryInterface;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\AppSettingRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyMemberRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyStreamRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Repositories\Eloquent\AdminAccessRepository;
use MetaFox\Core\Repositories\Eloquent\AdminSearchRepository;
use MetaFox\Core\Repositories\Eloquent\AttachmentFileTypeRepository;
use MetaFox\Core\Repositories\Eloquent\AttachmentRepository;
use MetaFox\Core\Repositories\Eloquent\DriverRepository;
use MetaFox\Core\Repositories\Eloquent\ItemStatisticsRepository;
use MetaFox\Core\Repositories\Eloquent\LinkRepository;
use MetaFox\Core\Repositories\Eloquent\SiteSettingRepository;
use MetaFox\Core\Repositories\Eloquent\StatsContentRepository;
use MetaFox\Core\Repositories\Eloquent\StatsContentTypeAdminRepository;
use MetaFox\Core\Repositories\ItemStatisticsRepositoryInterface;
use MetaFox\Core\Repositories\LinkRepositoryInterface;
use MetaFox\Core\Repositories\PrivacyMemberRepository;
use MetaFox\Core\Repositories\PrivacyPolicyRepository;
use MetaFox\Core\Repositories\PrivacyRepository;
use MetaFox\Core\Repositories\PrivacyStreamRepository;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;
use MetaFox\Core\Support\AttachmentFileType;
use MetaFox\Core\Support\BanWord;
use MetaFox\Core\Support\Content\BBCode;
use MetaFox\Core\Support\ConverterManager;
use MetaFox\Core\Support\EmojiSupport;
use MetaFox\Core\Support\FileSystem\FileType;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Core\Support\Input;
use MetaFox\Core\Support\Output;
use MetaFox\Core\Support\UniqueId;
use MetaFox\Core\Support\UrlUtility;
use MetaFox\Platform\Contracts\BanWord as BanWordContract;
use MetaFox\Platform\Contracts\BBCode as BBCodeContract;
use MetaFox\Platform\Contracts\Input as InputContract;
use MetaFox\Platform\Contracts\MetaFoxFileTypeInterface;
use MetaFox\Platform\Contracts\Output as OutputContract;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\ResizeImageInterface;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Contracts\UniqueIdInterface;
use MetaFox\Platform\Contracts\UploadFile as UploadFileContract;
use MetaFox\Platform\Contracts\UrlUtilityInterface;
use MetaFox\Platform\Facades\Profiling;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class CoreServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var string[]
     */
    public array $singletons = [
        'core.drivers'                                  => DriverRepository::class,
        DriverRepositoryInterface::class                => DriverRepository::class,
        UniqueIdInterface::class                        => UniqueId::class,
        PrivacyRepositoryInterface::class               => PrivacyRepository::class,
        PrivacyMemberRepositoryInterface::class         => PrivacyMemberRepository::class,
        PrivacyStreamRepositoryInterface::class         => PrivacyStreamRepository::class,
        StatsContentRepositoryInterface::class          => StatsContentRepository::class,
        PrivacyPolicy::class                            => PrivacyPolicyRepository::class,
        UploadFileContract::class                       => UploadFile::class,
        ResizeImageInterface::class                     => ResizeImage::class,
        AppSettingRepositoryInterface::class            => AppSettingRepository::class,
        UrlUtilityInterface::class                      => UrlUtility::class,
        LinkRepositoryInterface::class                  => LinkRepository::class,
        AttachmentRepositoryInterface::class            => AttachmentRepository::class,
        AttachmentFileTypeRepositoryInterface::class    => AttachmentFileTypeRepository::class,
        AdminSearchRepositoryInterface::class           => AdminSearchRepository::class,
        AdminAccessRepositoryInterface::class           => AdminAccessRepository::class,
        ItemStatisticsRepositoryInterface::class        => ItemStatisticsRepository::class,
        OutputContract::class                           => Output::class,
        InputContract::class                            => Input::class,
        BanWordContract::class                          => BanWord::class,
        BBCodeContract::class                           => BBCode::class,
        SiteSettingRepositoryInterface::class           => SiteSettingRepository::class,
        AttachmentFileTypeContract::class               => AttachmentFileType::class,
        MetaFoxFileTypeInterface::class                 => FileType::class,
        'core.converter'                                => ConverterManager::class,
        StatsContentTypeAdminRepositoryInterface::class => StatsContentTypeAdminRepository::class,
        EmojiSupportInterface::class                    => EmojiSupport::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Profiling::tick(__METHOD__);
        /*
         * @see \MetaFox\Platform\Facades\Settings::getFacadeAccessor()
         */
        $this->app->alias('Settings', SiteSettingRepositoryInterface::class);

        $this->app->singleton(MetaFoxStore::class, function () {
            return new MetaFoxStore();
        });

        $this->app->booted(function () {
            try {
                resolve(DriverRepositoryInterface::class)
                    ->bootingKernelConfigs();
            } catch (Exception $exception) {
            }
        });

        $this->app->booted(function () {
            try {
                Settings::bootingKernelConfigs();
            } catch (Exception) {
                // skip exception
            }
        });

        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\Core\Support\LoadMissingPrivacyMember::class,
                \MetaFox\Core\Support\LoadMissingItemStatistic::class,
            ]);
        });

        Profiling::end(__METHOD__);
    }

    /**
     * Bootstrap bind repositories.
     *
     * @return void
     */
    public function boot(): void
    {
        Privacy::observe(EloquentModelObserver::class);
        PrivacyMember::observe(EloquentModelObserver::class);
        Link::observe([EloquentModelObserver::class]);
        Attachment::observe([AttachmentObserver::class]);
        Driver::observe(EloquentModelObserver::class);
        SiteSetting::observe(EloquentModelObserver::class);

        Relation::morphMap([
            Attachment::IMPORTER_ENTITY_TYPE => Attachment::class,
        ]);

        if (app()->runningUnitTests()) {
            \MetaFox\Platform\Tests\Mock\Models\ContentModel::observe(EloquentModelObserver::class); // issuer installation process
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [MetaFoxStore::class];
    }
}
