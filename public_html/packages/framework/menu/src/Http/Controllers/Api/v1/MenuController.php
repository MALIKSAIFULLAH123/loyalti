<?php

namespace MetaFox\Menu\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Menu\Repositories\Eloquent\MenuItemRepository;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Menu\Http\Controllers\Api\MenuController::$controllers.
 */

/**
 * Class MenuController.
 * @codeCoverageIgnore
 * @ignore
 */
class MenuController extends ApiController
{
    /**
     * @var MenuItemRepository
     */
    private MenuItemRepository $itemRepository;

    /**
     * @param MenuItemRepository $itemRepository
     */
    public function __construct(MenuItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Show Menu.
     * @param  string       $menuName
     * @return JsonResponse
     */
    public function showMenu(string $menuName): JsonResponse
    {
        $data = $this->itemRepository->loadItems($menuName, 'web');

        return $this->success($data);
    }
}
