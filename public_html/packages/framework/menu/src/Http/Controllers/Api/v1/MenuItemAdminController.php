<?php

namespace MetaFox\Menu\Http\Controllers\Api\v1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Menu\Http\Requests\v1\MenuItem\Admin\IndexRequest;
use MetaFox\Menu\Http\Requests\v1\MenuItem\Admin\StoreRequest;
use MetaFox\Menu\Http\Requests\v1\MenuItem\Admin\UpdateRequest;
use MetaFox\Menu\Http\Resources\v1\MenuItem\Admin\MenuItemDetail as Detail;
use MetaFox\Menu\Http\Resources\v1\MenuItem\Admin\MenuItemItemCollection as ItemCollection;
use MetaFox\Menu\Http\Resources\v1\MenuItem\Admin\StoreMenuItemForm;
use MetaFox\Menu\Http\Resources\v1\MenuItem\Admin\UpdateMenuItemForm;
use MetaFox\Menu\Models\Menu;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\MenuItemAdminController::$controllers.
 */

/**
 * Class MenuItemAdminController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @group admin/menu/item
 * @ignore
 * @authenticated
 */
class MenuItemAdminController extends ApiController
{
    /**
     * @var MenuItemRepositoryInterface
     */
    private MenuItemRepositoryInterface $repository;

    /**
     * @var MenuRepositoryInterface
     */
    private MenuRepositoryInterface $menuRepository;

    /**
     * @param MenuItemRepositoryInterface $repository
     * @param MenuRepositoryInterface     $menuRepository
     */
    public function __construct(
        MenuItemRepositoryInterface $repository,
        MenuRepositoryInterface     $menuRepository,
    )
    {
        $this->repository     = $repository;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Retry menu items.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params         = $request->validated();
        $search         = Arr::get($params, 'q');
        $menuId         = Arr::get($params, 'menu_id', 0);
        $menuItemId     = Arr::get($params, 'menu_item_id', 0);
        $packageId      = Arr::get($params, 'package_id');
        $resolution     = Arr::get($params, 'resolution');
        $isActive       = Arr::get($params, 'is_active');
        $menu           = $this->menuRepository->getModel()->newModelQuery()->find($menuId);
        $parentMenuItem = $this->repository->getModel()->newModelQuery()->find($menuItemId);
        $excludedByAs   = Arr::get($params, 'excluded_as', ['sidebarButton']);
        $forceOrderBy   = $menu instanceof Menu ? Arr::get($menu->extra, 'order_by') : null;

        if ($parentMenuItem instanceof MenuItem) {
            $forceOrderBy = Arr::get($parentMenuItem->extra, 'order_by') ?: null;
        }

        $query = $this->repository->getModel()
            ->newModelQuery()
            ->from('core_menu_items as item')
            ->leftJoin('core_menu_items as sub', function (JoinClause $join) {
                $join->on('item.menu', '=', 'sub.menu');
                $join->on('item.resolution', '=', 'sub.resolution');
                $join->on('item.name', '=', 'sub.parent_name');
            });

        if (is_array($excludedByAs) && count($excludedByAs)) {
            $query->where(function (Builder $builder) use ($excludedByAs) {
                $builder->whereNull('item.as')
                    ->orWhereNotIn('item.as', $excludedByAs);
            });
        }

        if ($search) {
            $query->leftJoin("phrases as ps", "ps.key", '=', "item.label")
                ->where(function (Builder $builder) use ($search) {
                    $likeOperator  = $this->repository->likeOperator();
                    $defaultLocale = Language::getDefaultLocaleId();

                    $builder->where('ps.locale', '=', $defaultLocale);
                    $builder->where(function (Builder $query) use ($search, $likeOperator) {
                        $query->orWhere("item.name", $likeOperator, "%$search%");
                        $query->orWhere('ps.text', $likeOperator, "%$search%");
                    });

                    $builder->orWhere(function (Builder $query) use ($search, $likeOperator) {
                        $query->whereNull('ps.id');
                        $query->where("item.label", $likeOperator, "%$search%");
                    });
                });
        }

        if ($menu instanceof Menu) {
            $query = $query->where('item.menu', '=', $menu->name)->where('item.resolution', $menu->resolution);
        }

        $query = match ($parentMenuItem instanceof MenuItem) {
            true  => $query->where('item.parent_name', '=', $parentMenuItem->name)->where('item.resolution', $parentMenuItem->resolution),
            false => $query->where(function (Builder $subQuery) {
                $subQuery->whereNull('item.parent_name')->orWhere('item.parent_name', '=', '');
            })
        };

        if ($packageId) {
            $query = $query->where('item.package_id', '=', $packageId);
        }

        if ($resolution) {
            $query = $query->where('item.resolution', '=', $resolution);
        }

        if (null !== $isActive) {
            $query = $query->where('item.is_active', '=', $isActive ? 1 : 0);
        }

        $packageScope = new PackageScope($this->repository->getModel()->getTable());
        $packageScope->setAliasTable('item');

        $query->addScope($packageScope);
        
        $items = $query
            ->groupBy('item.id')
            ->orderBy('item.ordering')
            ->orderBy('item.label')
            ->get(['item.*', DB::raw('count(item.id) as child_count')]);

        if ($forceOrderBy !== null) {
            $items = collect($items)->sortBy(fn (MenuItem $item) => __p($item->label));
        }

        return $this->success(new ItemCollection($items));
    }

    protected function enablePaginationForBrowse(?string $menu): bool
    {
        if (null === $menu) {
            return false;
        }

        return in_array($menu, ['core.adminSidebarMenu']);
    }

    /**
     * Create menu item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params     = $request->validated();
        $parentName = Arr::get($params, 'menu');
        $resolution = Arr::get($params, 'resolution', 'web');

        $menu = $this->menuRepository
            ->getModel()
            ->newModelQuery()
            ->where('name', $parentName ?? '')
            ->where('resolution', $resolution)
            ->first();

        if (!$menu instanceof Menu) {
            abort(400, __p('menu::phrase.parent_menu_does_not_exist'));
        }

        $params = array_merge([
            'resolution' => $menu->resolution,
        ], $params);

        if ($menu->resolution !== MetaFoxConstant::RESOLUTION_MOBILE) {
            if (Arr::has($params, 'iconColor')) {
                Arr::forget($params, 'iconColor');
            }
        }

        $menuItem = $this->repository->createMenuItem($params);

        $url = sprintf('menu/menu/%s/menu-item/browse', $menu->entityId());

        if ($menuItem->parent_name) {
            $parentMenu = $this->repository->where([
                'menu'       => $menuItem->menu,
                'name'       => $menuItem->parent_name,
                'resolution' => $menuItem->resolution,
            ])->first();

            $url = $parentMenu instanceof MenuItem
                ? sprintf('menu/menu_item/%s/child/browse', $parentMenu->entityId())
                : $url;
        }

        Artisan::call('cache:reset');

        $this->navigate($url);

        return $this->success(new Detail($menuItem), [], __p('menu::phrase.menu_item_created_successfully'));
    }

    /**
     * View menu item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function show(int $id): JsonResponse
    {
        $menuItem = $this->repository->find($id);

        return $this->success(new Detail($menuItem));
    }

    /**
     * Update menu item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateMenuItem($id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('menu::phrase.menu_item_updated_successfully'));
    }

    /**
     * Delete menu item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return $this->success([], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * Get the creation form.
     *
     * @param int|null $id
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function edit(int $id = null): JsonResponse
    {
        $menuItem = $this->repository->find($id);

        return $this->success(new UpdateMenuItemForm($menuItem));
    }

    /**
     * Get the creation form.
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function create(int $parentId): JsonResponse
    {
        $menu = $this->menuRepository->find($parentId);

        $form = resolve(StoreMenuItemForm::class, ['parentMenu' => $menu ?: null]);

        return $this->success($form);
    }

    /**
     * Get the creation form.
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function createChild(int $parentId): JsonResponse
    {
        $menu = $this->repository->find($parentId);

        $form = resolve(StoreMenuItemForm::class, ['parentMenu' => $menu ?: null]);

        return $this->success($form);
    }

    /**
     * Active menu item.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @group admin/menu
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $resource = $this->repository->update([
            'is_active' => $params['active'],
        ], $id);

        Artisan::call('cache:reset');

        return $this->success(new Detail($resource), [], __p('core::phrase.already_saved_changes'));
    }

    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderItems($orderIds);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('menu::phrase.menus_successfully_ordered'));
    }
}
