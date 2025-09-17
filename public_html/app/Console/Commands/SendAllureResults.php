<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendAllureResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'allure:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send allure results to report server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dir = base_path('build/allure-results');

        if (!is_dir($dir)) {
            $this->comment('path not found ' . $dir);

            return 0;
        }
        $files = app('files')->files($dir);

        if (!count($files)) {
            $this->comment('there are no allure result files');

            return 0;
        }

        $baseUrl = config('app.allure_server');
        $id      = config('app.allure_project_id');

        if (!$id || !$baseUrl) {
            $this->error('missing env "ALLURE_SERVER, ALLURE_PROJECT"');

            return 1;
        }

        $serverUrl = sprintf('%s/allure-docker-service/send-results?project_id=%s', $baseUrl, $id);

        $this->info(sprintf('Sending %d results to "%s"', count($files), $serverUrl));
        $chunks = array_chunk($files, 100);

        $data  = [];

        foreach ($chunks as $chunk) {
            $http = Http::asMultipart();

            $this->comment(sprintf('Sending %s files', count($chunk)));
            foreach ($chunk as $file) {
                $http->attach('files[]', $file->getContents(), $file->getFilename());
            }

            $request = $http->timeout(30)
                ->post($serverUrl);

            if ($this->option('verbose')) {
                $this->comment(json_encode($request->json(), JSON_PRETTY_PRINT));
            }
            $status = $request->status();
            $this->comment(sprintf('Response status: %s', $status));
        }

        $request = Http::asJson()->get(sprintf('%s/allure-docker-service/generate-report?project_id=%s', $baseUrl, $id));

        if ($this->option('verbose')) {
            $this->comment(json_encode($request->json(), JSON_PRETTY_PRINT));
        }

        return 0;
    }

    protected function getOptions()
    {
        return [
        ];
    }
}
