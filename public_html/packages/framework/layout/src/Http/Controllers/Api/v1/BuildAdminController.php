<?php

namespace MetaFox\Layout\Http\Controllers\Api\v1;

use App\Setup\State;
use Illuminate\Http\JsonResponse;
use MetaFox\App\Repositories\Eloquent\PackageRepository;
use MetaFox\Layout\Http\Requests\v1\Build\Admin\IndexRequest;
use MetaFox\Layout\Http\Requests\v1\Build\Admin\StoreRequest;
use MetaFox\Layout\Http\Requests\v1\Build\Admin\UpdateRequest;
use MetaFox\Layout\Http\Resources\v1\Build\Admin\BuildDetail as Detail;
use MetaFox\Layout\Http\Resources\v1\Build\Admin\BuildItem;
use MetaFox\Layout\Http\Resources\v1\Build\Admin\CreateBuild as CreateBuildForm;
use MetaFox\Layout\Jobs\CheckBuild;
use MetaFox\Layout\Jobs\CreateBuild;
use MetaFox\Layout\Models\Build;
use MetaFox\Layout\Repositories\BuildRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Layout\Http\Controllers\Api\BuildAdminController::$controllers;.
 */

/**
 * Class BuildAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BuildAdminController extends ApiController
{
    /**
     * @var BuildRepositoryInterface
     */
    private BuildRepositoryInterface $repository;

    /**
     * BuildAdminController Constructor.
     *
     * @param BuildRepositoryInterface $repository
     */
    public function __construct(BuildRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->repository->checkExpiredTasks();
    }

    // does not need to verfy bundle task per id. scan it all.`
    public function check(): JsonResponse
    {
        $job = resolve(CheckBuild::class);

        $this->dispatchSync($job);

        $this->navigate('reload');

        return $this->success([]);
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $data   = $this->repository->orderBy('id', 'desc')
            ->paginate($params['limit'] ?? 20);

        return $this->success(BuildItem::collection($data));
    }

    public function create(): JsonResponse
    {
        return $this->success(new CreateBuildForm());
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $job = new CreateBuild(__p('layout::phrase.rebuild_site_action'));
        $this->dispatchSync($job);

        $this->navigate('/layout/build/browse', true);

        \App\Setup\State::preBuildFrontend();

        return $this->success([
            'data' => $job->getResponse(),
        ], [], 'Waiting to done.');
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return new Detail($data);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        /** @var Build $task */
        $task = $this->repository->find((int) $id);

        $task->delete();

        return $this->success([
            'id' => $id,
        ]);
    }

    public function wizard()
    {
        $steps        = [];
        $env          = resolve(PackageRepository::class)->getBuildEnvironments();
        $buildService = config('app.mfox_bundle_service_url');
        $appChannel   = config('app.mfox_app_channel');
        $info         = view('layout::wizard.info', compact('env', 'buildService', 'appChannel'))->render();

        // step 1.
        $steps[] = [
            'title'     => __p('layout::phrase.build_step_information'),
            'component' => 'ui.step.info',
            'expanded'  => true,
            'props'     => [
                'html'        => $info,
                'submitLabel' => __p('core::phrase.continue'),
                'hasSubmit'   => true,
            ],
        ];

        // step 2.
        $steps[] = [
            'title'     => __p('layout::phrase.build_step_processing'),
            'component' => 'ui.step.processes',
            'props'     => [
                'steps' => [
                    [
                        'title'      => __p('layout::phrase.build_step_processing_post_build'),
                        'dataSource' => ['apiUrl' => '/admincp/layout/build', 'apiMethod' => 'POST'],
                    ],
                    [
                        'title'            => __p('layout::phrase.build_step_processing_waiting'),
                        'disableUserAbort' => true,
                        'dataSource'       => ['apiUrl' => '/admincp/layout/build/waiting', 'apiMethod' => 'GET'],
                    ],
                ],
            ],
        ];

        $steps[] = [
            'title'     => __p('layout::phrase.build_step_done'),
            'component' => 'ui.step.info',
            'props'     => [],
        ];

        $data = [
            'title'       => __p('core::phrase.rebuild_site'),
            'description' => __p('layout::phrase.rebuite_site_guide'),
            'component'   => 'ui.step.steppers',
            'props'       => [
                'steps' => $steps,
            ],

        ];

        return $this->success($data);
    }

    public function waiting()
    {
        $state = State::factory('wait-frontend');

        if (($result = $state->checkInProgress())) {
            return $this->success($result);
        }

        CheckBuild::dispatchSync();

        return $this->success(['retry' => true]);
    }
}
