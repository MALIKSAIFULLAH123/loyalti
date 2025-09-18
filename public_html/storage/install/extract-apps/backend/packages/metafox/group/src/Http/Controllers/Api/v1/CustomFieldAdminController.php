<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Resources\v1\CustomField\Admin\CreateFieldForm;
use MetaFox\Group\Http\Resources\v1\CustomField\Admin\DuplicateFieldForm;
use MetaFox\Group\Http\Resources\v1\CustomField\Admin\EditFieldForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\CustomFieldAdminController::$controllers;
 */

/**
 * Class CustomFieldAdminController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class CustomFieldAdminController extends ApiController
{
    public function __construct(protected FieldRepositoryInterface $fieldRepository) { }

    public function create(): JsonResponse
    {
        return $this->success(new CreateFieldForm());
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->fieldRepository->find($id);
        return $this->success(new EditFieldForm($item));
    }

    public function duplicate(int $id): JsonResponse
    {
        $item = $this->fieldRepository->find($id);

        $form = new DuplicateFieldForm($item);

        return $this->success($form);
    }
}
