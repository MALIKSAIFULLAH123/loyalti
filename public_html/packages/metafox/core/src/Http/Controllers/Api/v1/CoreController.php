<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Http\Requests\v1\Core\CustomPrivacyOptionRequest;
use MetaFox\Core\Http\Requests\v1\Core\SettingsRequest;
use MetaFox\Core\Http\Requests\v1\Core\StoreCustomPrivacyOptionRequest;
use MetaFox\Core\Http\Requests\v1\Core\UrlToRouteRequest;
use MetaFox\Core\Http\Requests\v1\Link\FetchRequest;
use MetaFox\Core\Http\Resources\v1\Privacy\CustomPrivacyOptionCollection;
use MetaFox\Core\Http\Resources\v1\Privacy\CustomPrivacyOptionItem;
use MetaFox\Core\Repositories\Contracts\AppSettingRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Repositories\Eloquent\DriverRepository;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Http\Controllers\HasRevisionTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use StdClass;

/**
 * Class CoreController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group core
 */
class CoreController extends ApiController
{
    use HasRevisionTrait;

    /**
     * @var AppSettingRepositoryInterface
     */
    private AppSettingRepositoryInterface $appSettingRepository;

    /**
     * @param AppSettingRepositoryInterface $appSettingRepository
     */
    public function __construct(AppSettingRepositoryInterface $appSettingRepository)
    {
        $this->appSettingRepository = $appSettingRepository;
    }

    /**
     * @param Request  $request
     * @param string   $formName
     * @param int|null $id
     *
     * @return JsonResponse
     * @link \MetaFox\Core\Http\Controllers\Api\v1\CoreAdminController::showForm()
     */
    public function showForm(Request $request, string $formName, $id = null)
    {
        if (!$formName) {
            return $this->error(__p('core::validation.could_not_find_form'));
        }

        /** @var AbstractForm $form */
        $driver = Cache::rememberForever(
            sprintf(__METHOD__ . $formName),
            fn() => resolve(DriverRepository::class)
                ->getDriver(Constants::DRIVER_TYPE_FORM, $formName, 'web'),
        );

        $form = resolve($driver);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form->toArray($request), $form->getFormMeta());
    }

    /**
     * @param Request  $request
     * @param string   $formName
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function showMobileForm(Request $request, string $formName, $id = null)
    {
        if (!$formName) {
            return $this->error(__p('core::validation.could_not_find_form'));
        }

        /** @var AbstractForm $form */
        $driver = app('core.drivers')->getDriver(Constants::DRIVER_TYPE_FORM, $formName, 'mobile');

        $form = resolve($driver);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form->toArray($request), $form->getFormMeta());
    }

    /**
     * View app settings.
     *
     * @param Request     $request
     * @param string|null $revision
     *
     * @return JsonResponse
     * @group core
     */
    public function mobileSettings(SettingsRequest $request, string $revision = null): JsonResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $locale = App::getLocale();
        $role   = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $latestRevision = $this->getLatestRevision(['mobile', $locale, $role->id, MetaFox::getApiVersion(), MetaFox::clientTheme(), $request->input('type')]);

        if ($latestRevision == $revision) {
            return $this->success(['keepCached' => 1]);
        }

        $settings = Cache::store('file')
            ->rememberForever($latestRevision, function () use ($request, $role) {
                return $this->appSettingRepository->getMobileSettings($request, $role);
            });

        $settings['revision'] = $latestRevision;

        return $this->success($settings);
    }

    /**
     * View frontend settings.
     *
     * @param Request     $request
     * @param string|null $revision
     *
     * @return JsonResponse
     * @group core
     */
    public function webSettings(SettingsRequest $request, string $revision = null): JsonResponse
    {
        try {
            $locale = App::getLocale();
            /** @var User $user */
            $user = Auth::user();
            $role = resolve(RoleRepositoryInterface::class)->roleOf($user);

            $latestRevision = $this->getLatestRevision(['web', $locale, $role->id, $request->input('type')]);

            if ($latestRevision == $revision) {
                return $this->keepCacheSuccess();
            }

            $settings = Cache::store('file')
                ->rememberForever("settings.$latestRevision", function () use ($request, $role) {
                    return $this->appSettingRepository->getWebSettings($request, $role);
                });

            $settings['revision'] = $latestRevision;

            return $this->success($settings);
        } catch (\Exception $exception) {
            Artisan::call('cache:reset');

            return $this->error($exception->getMessage());
        }
    }

    /**
     * View frontend settings.
     *
     * @param Request     $request
     * @param string|null $revision
     *
     * @return JsonResponse
     * @group core
     */
    public function adminSettings(SettingsRequest $request, string $revision = null): JsonResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $locale = App::getLocale();
        $role   = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $latestRevision = $this->getLatestRevision(['admin', $locale, $role->id, $request->input('type')]);

        if ($latestRevision == $revision) {
            return $this->keepCacheSuccess();
        }

        $settings = Cache::store('file')
            ->rememberForever("settings.$latestRevision", function () use ($request, $role) {
                return $this->appSettingRepository->getAdminSettings($request, $role);
            });

        $settings['revision'] = $latestRevision;

        return $this->success($settings);
    }

    /**
     * Get canonical URL.
     *
     * @param UrlToRouteRequest $request
     *
     * @return JsonResponse
     * @group core
     * @throws AuthenticationException
     */
    public function urlToRoute(UrlToRouteRequest $request): JsonResponse
    {
        $params = $request->validated();

        $url = $params['url'];

        $parts = parse_url($url);

        $route = Arr::get($parts, 'path', MetaFoxConstant::EMPTY_STRING);

        $queryString = Arr::get($parts, 'query', MetaFoxConstant::EMPTY_STRING);

        $queryParams = [];

        if (is_string($queryString)) {
            parse_str($queryString, $queryParams);
        }

        $result = app('events')->dispatch('parseRoute', [$url, $route, $queryParams], true);

        if ($result) {
            return $this->success($result);
        }

        return $this->error('route not found.');
    }

    /**
     * View user badge status.
     *
     * @throws AuthenticationException
     * @group core
     */
    public function status(): JsonResponse
    {
        $user = user();
        $data = new StdClass();

        app('events')
            ->dispatch('core.badge_counter', [$user, $data]);

        return $this->success($data);
    }

    /**
     * View link.
     *
     * @param FetchRequest $request
     *
     * @return JsonResponse
     * @group core
     */
    public function fetchLink(FetchRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = Auth::user() ?? UserFacade::getGuestUser();

        $url = $params['link'];

        $data = app('events')
            ->dispatch('core.parse_url', [$url, $context, Arr::only($params, 'owner_id')], true);

        if (empty($data)) {
            return $this->error(__p('core::phrase.invalid_link'));
        }

        return $this->success($data);
    }

    /**
     * @param string      $group
     * @param string|null $locale
     * @param string|null $revision
     *
     * @return JsonResponse
     */
    public function loadTranslation(string $group, string $locale = null, string $revision = null): JsonResponse
    {
        if (!config('app.mfox_installed')) {
            return $this->success([]);
        }

        $locale = $locale == 'auto' ? App::getLocale() : $locale;

        $latestRevision = $this->getLatestRevision(['web', $group, $locale]);

        if ($revision === $latestRevision) {
            return $this->keepCacheSuccess([
                'revision' => $latestRevision,
                '$locale'  => $locale,
            ]);
        }

        $data = app('translation.loader')->load($locale, $group, null);

        $data['revision'] = $latestRevision;
        $data['$locale']  = $locale;

        return $this->success($data);
    }

    public function getCustomPrivacyOptions(CustomPrivacyOptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $lists = resolve(PrivacyRepositoryInterface::class)->getCustomPrivacyOptions($context, $data);

        return $this->success(new CustomPrivacyOptionCollection($lists));
    }

    /**
     * @return JsonResponse
     * @hideFromAPIDocumentation
     */
    public function checkInstalled()
    {
        return $this->error('This site is installed');
    }

    public function createCustomPrivacyOption(StoreCustomPrivacyOptionRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $friendList = app('events')->dispatch('friend.friend_list.create', [$context, $params], true);

        if (null === $friendList) {
            return $this->error('', 403);
        }

        $friendList->is_selected = true;

        return $this->success(new CustomPrivacyOptionItem($friendList), [], __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('friend::phrase.friend_list')]
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getCustomPrivacyOptionForm(Request $request): JsonResponse
    {
        $resolution = MetaFox::isMobile() ? MetaFoxConstant::RESOLUTION_MOBILE : MetaFoxConstant::RESOLUTION_WEB;

        $form = app('events')->dispatch(
            'friend.friend_list.create_form',
            [$request, $resolution, 'core/custom-privacy-option'],
            true
        );

        if (null === $form) {
            throw new AuthorizationException();
        }

        return $this->success($form->toArray($request));
    }

    /**
     * Show data grid.
     *
     * @queryParam dataGrid string required Grid name. Example: phrase_admin
     *
     * @param Request $request
     * @param string  $gridName
     * @param mixed   $parentId
     *
     * @return JsonResponse
     */
    public function showDataGrid(Request $request, string $gridName, $parentId = null): JsonResponse
    {
        $resolution = match (MetaFox::isMobile()) {
            true    => MetaFoxConstant::RESOLUTION_MOBILE,
            default => MetaFoxConstant::RESOLUTION_WEB
        };

        $driver = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_DATA_GRID, \Str::snake($gridName), $resolution);

        if (!$driver) {
            throw new \InvalidArgumentException(__p('validation.invalid'));
        }

        $grid = new $driver($gridName);

        if (method_exists($grid, 'boot')) {
            app()->call([$grid, 'boot'], array_merge(['parentId' => $parentId], $request->route()->parameters()));
        }

        return $this->success($grid);
    }

    public function ping()
    {
        return ['data' => 'pong'];
    }

    public function webPartialSettings(string $type, string $revision): JsonResponse
    {
        try {
            $locale = App::getLocale();

            /** @var User $user */
            $user = Auth::user();

            /**
             * @var Role $role
             */
            $role = resolve(RoleRepositoryInterface::class)->roleOf($user);

            $latestRevision = $this->getLatestRevision(['web', $locale, $role->entityId(), $type]);

            if ($latestRevision == $revision) {
                return $this->keepCacheSuccess();
            }

            $settings = Cache::store('file')
                ->rememberForever("settings.$latestRevision", function () use ($role, $type) {
                    return $this->appSettingRepository->getSettingsByType($role, $type, 'web');
                });

            $settings['revision'] = $latestRevision;

            return $this->success($settings);
        } catch (\Exception $exception) {
            Artisan::call('cache:reset');

            return $this->error($exception->getMessage());
        }
    }

    public function adminPartialSettings(string $type, string $revision): JsonResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $locale = App::getLocale();
        $role   = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $latestRevision = $this->getLatestRevision(['admin', $locale, $role->id, $type]);

        if ($latestRevision == $revision) {
            return $this->keepCacheSuccess();
        }

        $settings = Cache::store('file')
            ->rememberForever("settings.$latestRevision", function () use ($type, $role) {
                return $this->appSettingRepository->getSettingsByType($role, $type, 'admin');
            });

        $settings['revision'] = $latestRevision;

        return $this->success($settings);
    }

    public function mobilePartialSettings(string $type, string $revision): JsonResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $locale = App::getLocale();
        $role   = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $latestRevision = $this->getLatestRevision(['mobile', $locale, $role->id, MetaFox::getApiVersion(), MetaFox::clientTheme(), $type]);

        if ($latestRevision == $revision) {
            return $this->success(['keepCached' => 1]);
        }

        $settings = Cache::store('file')
            ->rememberForever($latestRevision, function () use ($type, $role) {
                return $this->appSettingRepository->getSettingsByType($role, $type, 'mobile');
            });

        $settings['revision'] = $latestRevision;

        return $this->success($settings);
    }
}
