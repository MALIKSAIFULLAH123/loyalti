<?php

namespace MetaFox\HealthCheck\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Support\Facades\License;
use MetaFox\HealthCheck\Support\Facades\NoticeManager;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Resolver;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\PackageManager;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\HealthCheck\Http\Controllers\Api\CheckAdminController::$controllers;.
 */

/**
 * Class CheckAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class CheckAdminController extends ApiController
{
    public function overview()
    {
        Artisan::call('metafox:health-check');

        $message = Artisan::output();

        return $this->success([
            'title' => 'System Overview',
            'items' => [
                ['label' => $message],
            ],
        ]);
    }

    public function wizard()
    {
        $checkers = Arr::flatten(PackageManager::discoverSettings('getCheckers'));

        $steps = [];
        foreach ($checkers as $className) {
            /** @var Checker $checker */
            $checker = resolve($className);

            $steps[] = [
                'title'        => $checker->getName(),
                'dryRun'       => true,
                'disableRetry' => true,
                'enableReport' => true,
                'dataSource'   => [
                    'apiUrl'    => '/admincp/health-check/check',
                    'apiMethod' => 'POST',
                ],
                'data' => [
                    'id' => $checker::class,
                ],
            ];
        }

        return $this->success([
            'title'     => 'Health Check',
            'component' => 'ui.step.processes',
            'props'     => [
                'disableNavigateConfirm' => true,
                'steps'                  => $steps,
            ],

        ]);
    }

    public function check(Request $request)
    {
        $id = $request->get('id');

        if (!$id || !class_exists($id)) {
            return $this->success([]);
        }

        $ref = new \ReflectionClass($id);
        if (!$ref->isSubclassOf(Checker::class)) {
            return $this->success([]);
        }

        /** @var Checker $checker */
        $checker = $ref->newInstance();

        $result = $checker->check();

        $message = view('health-check::checker/step-report', [
            'reports' => $result->getReports(),
        ])->render();

        $status = $result->okay() ? 'success' : 'error';

        return $this->success(compact('message', 'status'));
    }

    public function notices()
    {
        $reports = NoticeManager::collectReports();

        return $this->success($reports);
    }

    public function license()
    {
        return $this->success(License::refresh(), [], __p('health-check::phrase.license_status_has_been_updated'));
    }

    public function resolve(string $name)
    {
        [, $class] = resolve(DriverRepositoryInterface::class)->loadDriver('health-check-resolver', $name, 'admin');
        if (empty($class) || !class_exists($class)) {
            throw new \RuntimeException('Resolver class not found.');
        }

        $resolver = resolve($class);
        if (!$resolver instanceof Resolver) {
            throw new \RuntimeException(sprintf('%s is not an instance of the Resolver class.', $class));
        }

        if (!$resolver->resolve()) {
            return $this->error($resolver->errorMessage());
        }

        return $this->success(['valid' => 1], [], $resolver->successMessage());
    }
}
