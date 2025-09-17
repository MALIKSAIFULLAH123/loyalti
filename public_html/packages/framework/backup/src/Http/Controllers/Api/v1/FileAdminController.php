<?php

namespace MetaFox\Backup\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Backup\Http\Resources\v1\File\Admin\CreateBackupForm;
use MetaFox\Backup\Http\Resources\v1\File\Admin\FileItemCollection as ItemCollection;
use MetaFox\Backup\Jobs\BackupProcessJob;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\DbTableHelper;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Backup\Http\Controllers\Api\FileAdminController::$controllers;.
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
    public function index(Request $request): ItemCollection
    {
        $disk = app('storage')->disk('backup');

        $files = $disk->files('metafox-backup');

        $files = array_filter($files, function ($file) {
            return preg_match('/(.*)backup-(.+)\.zip/m', $file);
        });
        $files = array_reverse($files);

        $todate = function ($filename) {
            $date = preg_replace('/(.*)backup-(.+)\.zip/m', '$2 ', $filename);
            $arr  = explode('-', $date);

            return Carbon::create($arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5]);
        };

        $files = array_map(function ($file) use ($disk, $todate) {
            return [
                'id'         => base64_encode($file),
                'filename'   => basename($file),
                'filesize'   => $disk->size($file),
                'created_at' => $todate($file),
            ];
        }, $files);

        $search = $request->get('q');

        if ($search) {
            $files = array_filter($files, function ($file) use ($search) {
                return preg_match('/(.+)/m', $search) || $file['id'] === $search;
            });
        }

        return new ItemCollection($files);
    }

    public function create(): JsonResponse
    {
        $form = new CreateBackupForm();

        return $this->success($form);
    }

    public function download(string $id)
    {
        $disk = app('storage')
            ->disk('backup');

        $filePath = base64_decode($id);

        if (!$disk->exists($filePath)) {
            abort(404, 'File not found');
        }

        $fileSize = $disk->size($filePath);
        $fileName = app('files')->basename($filePath);

        @ob_end_clean();

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $fileSize);
        header('Content-Description: File Transfer');
        header('Cache-Control: no-cache');
        header('Pragma: public');
        header('Expires: 0');
        header('Accept-Ranges: Bytes');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $stream = $disk->readStream($filePath);

        fpassthru($stream);

        die();
    }

    public function store(): JsonResponse
    {
        if (!Settings::get('backup.enable_backup', 1)) {
            abort(403);
        }

        BackupProcessJob::dispatch(user()->entityId());

        return $this->success([], [], __p('backup::phrase.backup_process_is_in_process'));
    }

    /**
     * Delete item.
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $disk = app('storage')->disk('backup');

        $filename = base64_decode($id);

        if ($disk->exists($filename)) {
            $disk->delete($filename);
        }

        return $this->success([
            'id' => $id,
        ]);
    }

    public function prepare()
    {
        $disk = app('storage')->disk('backup');

        if (!$disk) {
            //
        }

        $config = config('backup');

        $lines = [];

        $lines[] = 'Backup Database ' . DB::getDatabaseName();

        $lines[] = 'Backup Files';
        foreach (Arr::get($config, 'backup.source.files.include', []) as $path) {
            $lines[] = './' . substr($path, strlen(base_path()) + 1);
        }

        return $this->success([
            implode(PHP_EOL, $lines),
        ]);
    }

    public function wizard()
    {
        if (!Settings::get('backup.enable_backup', 1)) {
            abort(403);
        }

        $backupFile = app('storage')->disk('backup')->path('/');

        $steps = [
            [
                'id'        => 'information',
                'title'     => 'Backup Contents',
                'component' => 'ui.step.info',
                'props'     => [
                    'html'        => view('backup::wizard.info', [
                        'dbName'     => DB::getDatabaseName(),
                        'dbDriver'   => DB::getDriverName(),
                        'dbVersion'  => DbTableHelper::getDriverVersion(),
                        'dbSize'     => human_readable_bytes(DbTableHelper::getDatabaseSize()),
                        'backupFile' => $backupFile,
                    ])->render(),
                    'hasSubmit'   => true,
                    'submitLabel' => __p('core::phrase.continue'),
                ],
            ],
            [
                'id'        => 'processing',
                'title'     => 'Processing',
                'component' => 'ui.step.processes',
                'props'     => [
                    'hasSubmit'   => true,
                    'submitLabel' => __p('core::phrase.continue'),
                    'steps'       => [
                        [
                            'title'      => 'Process Backup',
                            'dataSource' => ['apiUrl' => '/admincp/backup/file', 'apiMethod' => 'POST'],
                        ],
                    ],
                ],
            ],
            [
                'id'        => 'waiting',
                'title'     => __p('backup::phrase.backup_process_is_in_process'),
                'component' => 'ui.step.info',
            ],
        ];

        return $this->success([
            'title'     => 'Backup Wizard',
            'component' => 'ui.step.steppers',
            //            'description'=> __p('backup::phrase.backup_wizard_guide'),
            'props'     => [
                'steps' => $steps,
            ],
        ]);
    }
}
