<?php

namespace MetaFox\Rad\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\App\Http\Resources\v1\Package\Admin\PackageDetail as Detail;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeApiControllerRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeCategoryRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeDataGridRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeFormRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeImporterRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeInspectRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeJobRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeListenerRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeMailRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeMigrationRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeModelRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeNotificationRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakePackageRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakePolicyRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeRequestRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeRuleRequest;
use MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeSeederRequest;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeAdminApiForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeCategoryForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeDataGridForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeFormForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeImporterForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeJobForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeListenerForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeMailForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeMigrationForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeModelForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeNotificationForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakePackageForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakePolicyForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeRequestForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeRuleForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeSeederForm;
use MetaFox\Rad\Http\Resources\v1\Code\Admin\MakeWebApiForm;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\ModuleAdminController::$controllers.
 */

/**
 * Class CodeAdminController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @group admin/code
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @admincp
 */
class CodeAdminController extends ApiController
{
    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $repository;

    /**
     * ModuleAdminController constructor.
     *
     * @param PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return JsonResponse
     */
    public function makeModelForm(): JsonResponse
    {
        return $this->success(new MakeModelForm());
    }

    /**
     * @param  MakeModelRequest $request
     * @return JsonResponse
     */
    public function makeModel(MakeModelRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-model', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeRuleForm(): JsonResponse
    {
        return $this->success(new MakeRuleForm());
    }

    /**
     * @param  MakeRuleRequest $request
     * @return JsonResponse
     */
    public function makeRule(MakeRuleRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-rule', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makePolicyForm(): JsonResponse
    {
        return $this->success(new MakePolicyForm());
    }

    /**
     * @param  MakePolicyRequest $request
     * @return JsonResponse
     * @link \App\Console\Commands\MakePolicyCommand
     */
    public function makePolicy(MakePolicyRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-policy', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @param  MakeCategoryRequest $request
     * @return JsonResponse
     */
    public function makeCategory(MakeCategoryRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-category', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     * @link makeMigration
     */
    public function makeMigrationForm(): JsonResponse
    {
        return $this->success(new MakeMigrationForm());
    }

    /**
     * Make migration.
     *
     * @param  MakeMigrationRequest $request
     * @return JsonResponse
     * @link \App\Console\Commands\MakeMigrationCommand
     */
    public function makeMigration(MakeMigrationRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-migration', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeMailForm(): JsonResponse
    {
        return $this->success(new MakeMailForm());
    }

    /**
     * @param  MakeMailRequest $request
     * @return JsonResponse
     */
    public function makeMail(MakeMailRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-mail', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeNotificationForm(): JsonResponse
    {
        return $this->success(new MakeNotificationForm());
    }

    /**
     * @param  MakeNotificationRequest $request
     * @return JsonResponse
     */
    public function makeNotification(MakeNotificationRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-notification', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeJobForm(): JsonResponse
    {
        return $this->success(new MakeJobForm());
    }

    /**
     * @return JsonResponse
     */
    public function makeImporterForm(): JsonResponse
    {
        return $this->success(new MakeImporterForm());
    }

    /**
     * @param  MakeJobRequest $request
     * @return JsonResponse
     */
    public function makeJob(MakeJobRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-job', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @param  MakeImporterRequest $request
     * @return JsonResponse
     */
    public function makeImporter(MakeImporterRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-importer', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeSeederForm(): JsonResponse
    {
        return $this->success(new MakeSeederForm());
    }

    /**
     * @param  MakeSeederRequest $request
     * @return JsonResponse
     */
    public function makeSeeder(MakeSeederRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-seeder', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeListenerForm(): JsonResponse
    {
        return $this->success(new MakeListenerForm());
    }

    /**
     * @param  MakeListenerRequest $request
     * @return JsonResponse
     */
    public function makeListener(MakeListenerRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-listener', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeRequestForm(): JsonResponse
    {
        return $this->success(new MakeRequestForm());
    }

    /**
     * Make form request class.
     *
     * @param MakeRequestRequest $request
     *
     * @return JsonResponse
     * @link makeRequestForm
     */
    public function makeRequest(MakeRequestRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-request', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeCategoryForm(): JsonResponse
    {
        return $this->success(new MakeCategoryForm());
    }

    /**
     * @return JsonResponse
     */
    public function makeAdminApiForm(): JsonResponse
    {
        return $this->success(new MakeAdminApiForm());
    }

    /**
     * @return JsonResponse
     */
    public function makeWebApiForm(): JsonResponse
    {
        return $this->success(new MakeWebApiForm());
    }

    /**
     * @param MakeApiControllerRequest $request
     *
     * @return JsonResponse
     * @link \App\Console\Commands\MakeApiControllerCommand
     */
    public function makeApiController(MakeApiControllerRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-api-controller', $params);

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => 'Alert',
                'message'  => Artisan::output(),
            ],
        ]);
    }

    /**
     * Show make data grid form configuration.
     *
     * @return JsonResponse
     * @apiResource MakeDataGridForm
     * @link        makeDataGrid
     */
    public function makeDataGridForm(): JsonResponse
    {
        return $this->success(new MakeDataGridForm());
    }

    /**
     * @return JsonResponse
     */
    public function makeFormForm(): JsonResponse
    {
        return $this->success(new MakeFormForm());
    }

    /**
     * Show make package form configuration.
     *
     * @return JsonResponse
     * @apiResource MakePackageForm
     * @link        makePackage
     */
    public function makePackageForm(): JsonResponse
    {
        return $this->success(new MakePackageForm());
    }

    /**
     * Generate language package.
     *
     * @param MakePackageRequest $request
     *
     * @return JsonResponse
     * @link \App\Console\Commands\MakePackageCommand
     */
    public function makePackage(MakePackageRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $package = $params['package'];

        Artisan::call('package:make', $params);

        $module = resolve('core.packages')
            ->setupPackage($package);

        Artisan::call('optimize:clear');

        return $this->success(new Detail($module));
    }

    /**
     * Make data grid configuration class.
     *
     * @param MakeDataGridRequest $request
     *
     * @return JsonResponse
     *
     * @link makeDataGridForm
     * @link \App\Console\Commands\MakeDataGridCommand
     */
    public function makeDataGrid(MakeDataGridRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-datagrid', $params);

        $message = Artisan::output();

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'message'  => $message,
            ],
        ]);
    }

    /**
     * @param  MakeFormRequest $request
     * @return JsonResponse
     * @link MakeFormForm
     * @link \App\Console\Commands\MakeFormCommand
     */
    public function makeForm(MakeFormRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('package:make-form', $params);

        $message = Artisan::output();

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => __p('core::phrase.alert'),
                'message'  => $message,
            ],
        ]);
    }

    /**
     * @param  MakeInspectRequest $request
     * @return JsonResponse
     * @link MakeInspectRequest
     * @link \App\Console\Commands\MetaFoxDevCommand
     */
    public function makeInspect(MakeInspectRequest $request): JsonResponse
    {
        $params = $request->validated();

        Artisan::call('dev', $params);

        $message = Artisan::output();

        return $this->success($params, [
            'alert' => [
                'maxWidth' => 'sm',
                'title'    => __p('core::phrase.alert'),
                'message'  => $message,
            ],
        ]);
    }

    public function ideFix(): JsonResponse
    {
        Artisan::call('ide:fix');
        Artisan::call('optimize:clear');

        return $this->success([], [], __p('core::phrase.save_changes'));
    }
}
