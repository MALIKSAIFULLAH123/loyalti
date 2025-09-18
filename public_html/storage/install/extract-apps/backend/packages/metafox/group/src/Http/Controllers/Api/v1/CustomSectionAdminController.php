<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Resources\v1\CustomSection\Admin\CreateSectionForm;
use MetaFox\Group\Http\Resources\v1\CustomSection\Admin\EditSectionForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\CustomSectionAdminController::$controllers;
 */

/**
 * Class CustomSectionAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class CustomSectionAdminController extends ApiController
{
    public function __construct(protected SectionRepositoryInterface $sectionRepository) { }

    public function create(): JsonResponse
    {
        return $this->success(new CreateSectionForm());
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->sectionRepository->find($id);
        return $this->success(new EditSectionForm($item));
    }
}
