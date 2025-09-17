<?php

namespace MetaFox\Layout\Jobs;

use App\Setup\State;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Layout\Models\Build;
use MetaFox\Layout\Repositories\BuildRepositoryInterface;
use MetaFox\Layout\Repositories\SnippetRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use Throwable;
use ZipArchive;

class CheckBuild extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private ?Build $task;

    private $response = null;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct()
    {
        parent::__construct();
        $repository = resolve(BuildRepositoryInterface::class);
        $this->task = $repository->findLast();
    }

    public function verifyBuildCompleteOnService(): bool
    {
        // run in multiple server required.
        $this->task->refresh();

        if (!$this->task->running()) {
            return false;
        }

        $verifyUrl = sprintf(
            '%s/api/v1/build/%s',
            config('app.mfox_bundle_service_url'),
            $this->task->job_id,
        );

        Log::channel('dev')->info($verifyUrl);

        $json = Http::asJson()
            ->connectTimeout(30)
            ->get($verifyUrl)
            ->json();

        $status = Arr::get($json, 'data.status');

        // check build is complete.
        return $status === 'completed';
    }

    public function verifyBundleDownloaded(): bool
    {
        // run in multiple server required.
        $this->task->refresh();

        if (!$this->task->running()) {
            return false;
        }

        if ($this->task->bundle_path) {
            return true;
        }

        $this->task->bundle_status = 'downloading';
        $this->task->saveQuietly();

        $storage    = Storage::disk('local');
        $bundlePath = sprintf('bundle/%s-%s', date('Y-m-d-hi'), 'bundle.zip');
        $storage->put($bundlePath, mf_get_contents($this->task->bundle_url));

        if ($this->task->log_url) {
            $this->task->result = $this->task->result . PHP_EOL . mf_get_contents($this->task->log_url);
        }

        $this->task->bundle_disk = 'local';
        $this->task->bundle_path = $bundlePath;

        $this->task->bundle_status = 'downloaded';
        $this->task->saveQuietly();

        return true;
    }

    public function verifyBundleExists(): bool
    {
        return Storage::disk('local')->exists($this->task->bundle_path);
    }

    public function copyToTempFile(): string
    {
        $disk = Storage::disk($this->task->bundle_disk);

        $tempFile = tempnam(sys_get_temp_dir(), 'metafox');

        $content = $disk->get($this->task->bundle_path);

        if (!$content) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find %s on disk %s',
                $this->task->bundle_path,
                $this->task->bundle_disk
            ));
        }
        file_put_contents($tempFile, $content);

        return $tempFile;
    }

    public function handle()
    {
        Log::channel('installation')->debug(__METHOD__);

        // there are no task
        if (!$this->task) {
            return;
        }

        if (!$this->task->running()) {
            return;
        }

        if ($this->task->expired()) {
            $this->task->bundle_status = 'deprecated';
            $this->task->saveQuietly();

            return;
        }

        if ($this->task->bundle_status === 'pending') {
            $this->sendToBuildService();

            return;
        }

        if (!$this->verifyBuildCompleteOnService()) {
            return;
        }

        if (!$this->verifyBundleDownloaded()) {
            return;
        }

        if (!$this->verifyBundleExists()) {
            return;
        }

        \App\Setup\State::preBuildFrontend();

        $tempFile = $this->copyToTempFile();

        $this->extractBundle($tempFile);

        $this->task->bundle_status = 'done';
        $this->task->save();

        \App\Setup\State::postBuildFrontend();

        @unlink($tempFile);
        $this->removeBundleFile();
    }

    private function removeBundleFile(): void
    {
        $disk = Storage::disk($this->task->bundle_disk);
        $disk->delete($this->task->bundle_path);
    }

    private function extractBundle($tempFile): void
    {
        $this->task->refresh();

        if (!$this->task->running()) {
            return;
        }

        $webDisk = Storage::disk('web');

        $archive = new ZipArchive();

        $archive->open($tempFile, ZipArchive::RDONLY);

        Log::channel('dev')->info(sprintf('Extrat %s files to webdisk "%s".', $archive->count(), $webDisk->path('/')));

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $filename = $archive->getNameIndex($index);

            try {
                $content = $archive->getFromIndex($index);
                if (str_ends_with('/', $filename) || strlen($content) === 0) {
                    continue;
                }
                Log::channel('dev')->debug(sprintf('Extract file "%s"', $filename));
                $webDisk->put($filename, $content);
            } catch (\Exception $exception) {
                Log::channel('dev')->error(sprintf('%s error %s', __METHOD__, $exception->getMessage()));
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        // there are no task
        if (!$this->task) {
            return;
        }

        Log::channel('dev')->error(sprintf('error: %s', $exception->getMessage()));

        $this->task->bundle_status = 'failed';
        $this->task->result        = sprintf('%s error %s', __METHOD__, $exception->getMessage());
        $this->task->save();
    }

    public function sendToBuildService()
    {
        $this->task->refresh();

        if (!$this->task->running()) {
            return false;
        }

        $baseUrl                   = config('app.mfox_bundle_service_url');
        $serviceUrl                = $baseUrl . '/api/v1/build';
        $this->task->bundle_status = 'sending';
        $this->task->save();

        $data = new \ArrayObject();
        $tmp  = tempnam(sys_get_temp_dir(), 'file'); // good
        $zip  = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE);
        $this->attachBuildArchive($data, $zip);
        resolve(SnippetRepositoryInterface::class)->attachBuildArchive($data, $zip, $this->task->reason);
        resolve(PackageRepositoryInterface::class)->attachBuildArchive($data, $zip, $this->task->reason);
        $zip->close(); // close archive first.

        // test only

        $stream = fopen($tmp, 'r');
        if (!$stream) {
            throw new \RuntimeException("Could not open archive temp $tmp");
        }
        Log::channel('dev')->info($serviceUrl, ['filesize' => filesize($tmp)]);
        Log::channel('dev')->info(json_encode($data->getArrayCopy()));

        $response = Http::asMultipart()
            ->connectTimeout(30)
            ->attach('file', $stream, 'file.zip')
            ->post($serviceUrl, [
                'name'     => 'data',
                'contents' => json_encode($data->getArrayCopy()),
            ]);

        $json = $response->json();

        fclose($stream);
        @unlink($tmp);

        Log::channel('dev')
            ->info(sprintf('%s response: %s', $serviceUrl, $response->body()));

        if (!is_array($json)
            || Arr::get($json, 'status') != 'success') {
            $this->task->bundle_status = 'failed';
            $this->task->saveQuietly();
            $this->task->result = $response->body();
            throw new \InvalidArgumentException(sprintf('build service error %s ', $response->body()));
        }

        $this->task->bundle_url    = Arr::get($json, 'data.bundleUrl');
        $this->task->log_url       = Arr::get($json, 'data.logUrl');
        $this->task->job_id        = Arr::get($json, 'data.id');
        $this->task->bundle_status = 'processing';
        $this->task->save();

        State::markStatusIsDone('build-frontend');
    }

    /**
     * @param \ArrayObject $data
     * @param ZipArchive   $zip
     * @return void
     * @codeCoverageIgnore
     */
    public function attachBuildArchive(\ArrayObject $data, ZipArchive $zip): void
    {
        $base = base_path('frontend');

        if (!is_dir($base)) {
            return;
        }

        $baseLength = strlen($base) + 1;

        $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS);
        /** @var \SplFileInfo[] $fileInfos */
        $fileInfos = new \RecursiveIteratorIterator($recursiveDirectoryIterator);

        foreach ($fileInfos as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $localName = substr($file->getPathname(), $baseLength);

            $zip->addFromString($localName, mf_get_contents($file->getPathname()));
        }
    }

    public function getResponse()
    {
        return $this->task?->toArray();
    }
}
