<?php

namespace MetaFox\Profile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Http\Requests\v1\Field\Admin\IndexRequest;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\FieldDetail as Detail;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\FieldItemCollection as ItemCollection;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Profile\Http\Controllers\Api\FieldAdminController::$controllers;.
 */

/**
 * class FieldBasicInfoAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class FieldBasicInfoAdminController extends ApiController
{
    /**
     * FieldAdminController Constructor.
     *
     * @param FieldRepositoryInterface   $repository
     * @param SectionRepositoryInterface $sectionRepository
     */
    public function __construct(
        protected FieldRepositoryInterface   $repository,
        protected SectionRepositoryInterface $sectionRepository,
    ) {}

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $section = $this->sectionRepository->getSectionByName('basic_info');

        if (!$section instanceof Section) {
            return $this->success([]);
        }

        Arr::set($params, 'section_id', $section->id);
        $data = $this->repository->viewFieldsInSectionSystem($params);

        return new ItemCollection($data);
    }

    /**
     * Update active status.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function toggleRequire(Request $request, int $id): JsonResponse
    {
        $params = ['is_required' => $request->get('active')];
        $item   = $this->repository->toggleActive($id, $params);

        return $this->success(new Detail($item), [], __p('core::phrase.already_saved_changes'));
    }
}
