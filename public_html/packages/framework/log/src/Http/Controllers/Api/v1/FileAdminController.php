<?php

namespace MetaFox\Log\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Log\Support\FileLogReader;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use SplFileInfo;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Log\Http\Controllers\Api\FileAdminController::$controllers.
 */

/**
 * Class FileAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class FileAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        $data = [];

        /** @var SplFileInfo[] $files */
        $files = app('files')->files(storage_path('logs'));

        usort($files, function (SplFileInfo $a, SplFileInfo $b) {
            return $a->getFilename() < $b->getFilename();
        });

        // todo filter out none standard log file.
        $re = '/-\d{4}-\d{2}-\d{2}\.log/m';
        $files = Arr::where($files, fn($file)=>preg_match($re, $file->getFilename()));

        $index = 0;
        foreach ($files as $file) {
            $name   = $file->getFilename();
            $key    = base64_encode($name);
            $data[] = [
                'id'          => ++$index,
                'filename'    => $name,
                'modified_at' => Carbon::createFromTimestamp($file->getMTime())->toAtomString(),
                'filesize'    => $file->getSize(),
                'links'       => [
                    'pageUrl'     => sprintf('/log/file/%s/msg/browse', $key),
                    'downloadUrl' => sprintf('/log/file/download/:%s', $key),
                ],
            ];
        }

        return $this->success($data);
    }

    /**
     * View item.
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $base = $request->get('file_id');
        if (!$base) {
            $base = $request->get('file');
        }
        $filename = base64_decode($base);

        $filename = storage_path('logs/' . $filename);

        if (!file_exists($filename)) {
            return $this->error('file not found', 404);
        }

        $logs = (new FileLogReader())->get($filename);

        return $this->success($logs->getArrayCopy());
    }

    /**
     * Delete item.
     *
     * @param  string       $file
     * @return JsonResponse
     */
    public function destroy(string $file): JsonResponse
    {
        return $this->success([
            'id' => $file,
        ]);
    }
}
