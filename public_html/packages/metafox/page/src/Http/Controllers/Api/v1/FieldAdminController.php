<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Page\Http\Resources\v1\CustomField\Admin\CreateFieldForm;
use MetaFox\Page\Http\Resources\v1\CustomField\Admin\DuplicateFieldForm;
use MetaFox\Page\Http\Resources\v1\CustomField\Admin\EditFieldForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Page\Http\Controllers\Api\FieldAdminController::$controllers;
 */

/**
 * Class FieldAdminController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class FieldAdminController extends ApiController
{
    public function __construct(protected FieldRepositoryInterface $repository)
    {
    }

    public function create(): JsonResponse
    {
        $form = new CreateFieldForm();

        return $this->success($form);
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new EditFieldForm($item);

        return $this->success($form);
    }

    public function duplicate(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new DuplicateFieldForm($item);

        return $this->success($form);
    }
}
