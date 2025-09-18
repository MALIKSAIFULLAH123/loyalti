<?php

namespace MetaFox\Page\Support;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Page\Contracts\PageContract;
use MetaFox\Page\Models\IntegratedModule;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Page\Support\Facade\Page as PageSupportFacade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

class PageSupport implements PageContract
{
    public const MENTION_REGEX = '^\[page=(.*?)\]^';

    public const SHARED_TYPE = 'page';

    public const DEFAULT_TAB_ABOUT     = 'about';
    public const DEFAULT_TAB_HOME      = 'home';
    public const UPDATE_PAGE_NAME_TYPE = 'update_page_name';

    /**
     * @var PageRepositoryInterface
     */
    protected PageRepositoryInterface             $repository;
    protected IntegratedModuleRepositoryInterface $integratedModuleRepository;

    /**
     * @param PageRepositoryInterface             $repository
     * @param IntegratedModuleRepositoryInterface $integratedModuleRepository
     */
    public function __construct(PageRepositoryInterface $repository, IntegratedModuleRepositoryInterface $integratedModuleRepository)
    {
        $this->repository                 = $repository;
        $this->integratedModuleRepository = $integratedModuleRepository;
    }

    public function getMentions(string $content): array
    {
        $userIds = [];

        try {
            preg_match_all(self::MENTION_REGEX, $content, $matches);
            $userIds = array_unique($matches[1]);
        } catch (Exception $e) {
            // Silent.
        }

        return $userIds;
    }

    public function getPagesForMention(array $ids): Collection
    {
        $collection = $this->repository->getModel()->newModelQuery()
            ->whereIn('id', $ids)
            ->get();

        return $collection->mapWithKeys(function ($page) {
            return [$page->entityId() => $page];
        });
    }

    public function getPageBuilder(User $user): Builder
    {
        return $this->repository->getPageBuilder($user);
    }

    /**
     * getListTypes.
     *
     * @return array<mixed>
     */
    public function getListTypes(): array
    {
        return Cache::rememberForever('pages_list_types', function () {
            $resourceName = 'feed_type';

            $integrationTypes = $this->getDefaultListTypes($resourceName);

            $menuItems = resolve(MenuItemRepositoryInterface::class)
                ->getMenuItemByMenuName('page.page.profileMenu', 'web', true);

            if ($menuItems->count()) {
                foreach ($menuItems as $menuItem) {
                    if (is_string($menuItem->name)) {
                        $model = Relation::getMorphedModel($menuItem->name);

                        if (null !== $model) {
                            $model = resolve($model);
                        }

                        if ($model instanceof Content) {
                            $integrationTypes[] = [
                                'id'            => $model->entityType(),
                                'resource_name' => $resourceName,
                                'name'          => __p($menuItem->label),
                            ];
                        }
                    }
                }
            }

            return $integrationTypes;
        });
    }

    /**
     * getDefaultListTypes.
     *
     * @param string $resourceName
     *
     * @return array<mixed>
     */
    protected function getDefaultListTypes(string $resourceName): array
    {
        if (!app_active('metafox/activity')) {
            return [];
        }

        $types[] = [
            'id'            => 'all',
            'resource_name' => $resourceName,
            'name'          => __p('core::phrase.all'),
        ];

        $postModel = Relation::getMorphedModel('activity_post');

        $linkModel = Relation::getMorphedModel('link');

        if (null !== $postModel) {
            $postModel = resolve($postModel);

            $types[] = [
                'id'            => $postModel->entityType(),
                'resource_name' => $resourceName,
                'name'          => __p('activity::phrase.posts'),
            ];
        }

        if (null !== $linkModel) {
            $linkModel = resolve($linkModel);

            $types[] = [
                'id'            => $linkModel->entityType(),
                'resource_name' => $resourceName,
                'name'          => __p('core::phrase.links'),
            ];
        }

        return $types;
    }

    public function isFollowing(User $context, User $user): bool
    {
        if (!app('events')->dispatch('follow.is_follow', [$context, $user], true)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getProfileMenuSettings(Page $page): array
    {
        return $this->integratedModuleRepository->getProfileMenuSettings($page->entityId());
    }

    public function getMemberBuilderForLoginAsPage(Page $page): Builder
    {
        return DB::table('user_entities')
            ->select('user_entities.id')
            ->join('page_members', function (JoinClause $joinClause) use ($page) {
                $joinClause->on('user_entities.id', '=', 'page_members.user_id')
                    ->where('page_members.page_id', '=', $page->entityId());
            })
            ->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($page) {
                $join->on('blocked_owner.owner_id', '=', 'user_entities.id')
                    ->where('blocked_owner.user_id', '=', $page->entityId());
            })
            ->whereNull('blocked_owner.owner_id');
    }

    public function getAllowApiRules(): array
    {
        $apiRules = [
            'q'           => ['truthy', 'q'],
            'sort'        => ['includes', 'sort', ['recent', 'most_viewed', 'most_member', 'most_discussed']],
            'type_id'     => ['truthy', 'type_id'], 'category_id' => ['truthy', 'category_id'],
            'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
            'is_featured' => ['truthy', 'is_featured'],
            'view'        => [
                'includes', 'view',
                [
                    Browse::VIEW_MY,
                    Browse::VIEW_FRIEND,
                    Browse::VIEW_PENDING,
                    Browse::VIEW_MY_PENDING,
                    Browse::VIEW_SEARCH,
                    ViewScope::VIEW_INVITED,
                    ViewScope::VIEW_LIKED,
                ],
            ],
        ];

        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        if (empty($fields)) {
            return $apiRules;
        }

        foreach ($fields as $field) {
            $apiRules[$field] = ['truthy', $field];
        }

        return $apiRules;
    }

    /**
     * @inheritDoc
     */
    public function getInfoSettingsSupportByResolution(string $resolution): array
    {
        $keys = [
            'name',
            'category_id',
            'profile_name',
            'text',
            'location',
            'external_link',
            'additional_information',
            'landing_page',
        ];

        return $keys;
    }

    public function allowHtmlOnDescription(): bool
    {
        return Settings::get('core.general.allow_html', true);
    }

    public function getCacheKeyDefaultTabActive(Page $page): string
    {
        return sprintf(
            '%s::getDefaultTabMenu(%s:%s)',
            get_class($page),
            $page->entityType(),
            $page->entityId(),
        );
    }

    public function getDefaultTabMenu(User $user, Page $page): string
    {
        $defaultActiveTabMenu = $page->landing_page ?? self::DEFAULT_TAB_HOME;

        if (!$page->isApproved()) {
            return self::DEFAULT_TAB_ABOUT;
        }

        return localCacheStore()->rememberForever(
            PageSupportFacade::getCacheKeyDefaultTabActive($page),
            function () use ($defaultActiveTabMenu, $page) {
                $integrateRepository = resolve(IntegratedModuleRepositoryInterface::class);
                $menus               = $integrateRepository->getModules($page->entityId());
                $item                = $menus->firstWhere('tab', $defaultActiveTabMenu);

                return $item instanceof IntegratedModule
                    ? $item->tab
                    : self::DEFAULT_TAB_ABOUT;
            }
        );
    }
}
