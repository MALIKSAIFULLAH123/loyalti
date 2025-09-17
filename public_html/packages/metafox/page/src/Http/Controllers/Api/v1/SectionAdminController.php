<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Page\Http\Resources\v1\CustomSection\Admin\CreateSectionForm;
use MetaFox\Page\Http\Resources\v1\CustomSection\Admin\EditSectionForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Page\Http\Controllers\Api\SectionAdminController::$controllers;
 */

/**
 * Class SectionAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class SectionAdminController extends ApiController
{
    public function __construct(protected SectionRepositoryInterface $repository)
    {
    }

    public function create(): JsonResponse
    {
        $form = new CreateSectionForm();

        return $this->success($form);
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new EditSectionForm($item);

        return $this->success($form);
    }
}
