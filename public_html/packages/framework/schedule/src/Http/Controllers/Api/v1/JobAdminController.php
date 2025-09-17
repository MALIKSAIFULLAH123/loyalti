<?php

namespace MetaFox\Schedule\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

use function Sodium\compare;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Schedule\Http\Controllers\Api\JobAdminController::$controllers.
 */

/**
 * Class JobAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class JobAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        Artisan::call('schedule:list', ['--next' => false]);

        $output = Artisan::output();

        $result = [];
        $re     = '/^\s*(?<schedule>[\d\s\*\/]{10,})(?<job>.+)Next Due:(?<next_due>.*)$/m';
        preg_match_all($re, $output, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $job          = rtrim($match['job'], " .\t\n\r\0\x0B");
            $result[$job] = [
                'id'       => count($result),
                'schedule' => $match['schedule'],
                'job'      => $job,
                'next_due' => $match['next_due'],
            ];
        }

//        ksort($result);

        return $this->success(array_values($result));
    }
}
