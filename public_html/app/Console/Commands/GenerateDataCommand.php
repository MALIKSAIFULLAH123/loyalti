<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Commands\UserGeneratorTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @SuppressWarnings(PHPMD)
 */
class GenerateDataCommand extends Command
{
    use UserGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'data:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample data.';

    private bool $testMode = false;

    private ?string $username;

    private int $sampleNeed = 1;

    private bool $verbose = false;

    private bool $linear = false;

    private ?User $testUser = null;

    /**
     * Number of user to cacualte ratio of other contents,
     * refer: $multiply * $ratio.
     * @var int
     */
    private int $multiply = 10;

    public function getMultiply()
    {
        $multiply = $this->option('multiply');
        if (!$multiply) {
            $multiply = 10;
        }

        return intval($multiply, 10);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        LoadReduce::disable();
        $this->testMode   = $this->option('test');
        $content          = $this->argument('content');
        $this->sampleNeed = (int) $this->option('count');
        $this->sampleNeed = $this->sampleNeed > 1 ? $this->sampleNeed : 1;
        $this->verbose    = (bool) $this->option('verbose') || $this->testMode;
        $this->linear     = $this->option('mode') === 'linear' && !$this->testMode;
        $this->multiply   = $this->getMultiply();

        if ($content === 'asset') {
            $this->downloadAssets();

            return 0;
        }

        if (!$this->initTestUser()) {
            return 1;
        }

        if ($content) {
            $modelClass = Relation::getMorphedModel($content);

            if (!$modelClass || !class_exists($modelClass)) {
                $this->error('Could not found realtion of ' . $content);

                return static::FAILURE;
            }

            $this->info(sprintf('Creating %s %s', number_format($this->sampleNeed), $content));

            $this->generateData($modelClass, $this->sampleNeed);

            return 0;
        }

        do {
            $okay = $this->processGenerateUntil();
        } while ($okay && !$this->testMode);

        return 0;
    }

    public function initTestUser(): bool
    {
        if (!$this->option('user')) {
            return true;
        }

        $username       = $this->option('user');
        $this->testUser = User::query()->where('id', (int) $username)
            ->orWhere('user_name', $username)
            ->orWhere('email', $username)
            ->orWhere('id', intval($username, 10))
            ->first();

        if (!$this->testUser) {
            $this->error('Invalid user ' . $username);

            return false;
        }

        return true;
    }

    public function processGenerateUntil(): bool
    {
        $file = $this->option('file');
        if (!$file) {
            $file = 'storage/framework/sample-data.php';
        }

        $file = base_path($file);

        if (!file_exists($file)) {
            $this->error('Failed to load generate data instruction ' . $file);

            return false;
        }

        $config = require $file;

        @ini_set('memory_limit', '-1');

        // generate page, user, comment, first.

        $createdCount = 0;

        foreach ($config as $content => $ratio) {
            $limit = ceil($ratio * $this->multiply);
            $chunk = 5;

            if (!$limit) {
                continue;
            }

            $modelClass = Relation::getMorphedModel($content);

            if (!$modelClass || !class_exists($modelClass)) {
                $this->error('Unexpected morphed model for ' . $content);

                continue;
            }

            $startTime = microtime(true);
            $need      = $this->needItem($modelClass, $limit, $chunk);
            $total     = $modelClass::count();
            $createdCount += $need;

            if ($this->linear) {
                while ($need > 0) {
                    $percent = $total / $limit * 100;
                    $this->generateData($modelClass, $need);

                    if ($this->verbose) {
                        $spendTime = microtime(true) - $startTime;
                        $this->info(sprintf(
                            'Created %s/%s %s (%.2f%%) - [%s ms]',
                            number_format($total),
                            number_format($limit),
                            $content,
                            $percent,
                            number_format($spendTime * 1000)
                        ));
                    }

                    $startTime = microtime(true);
                    $total     = $modelClass::count();
                    $need      = $this->needItem($modelClass, $limit, $chunk);
                }
            } elseif ($need > 0) {
                $percent   = $total / $limit * 100;
                $startTime = microtime(true);
                $this->generateData($modelClass, $need);
                $total = $modelClass::count();

                if ($this->verbose) {
                    $spendTime = microtime(true) - $startTime;
                    $this->info(sprintf(
                        'Created %s/%s %s (%.2f%%) - [%s ms]',
                        number_format($total),
                        number_format($limit),
                        $content,
                        $percent,
                        number_format($spendTime * 1000)
                    ));
                }
            }
        }

        $this->info(sprintf('Created %s items', number_format($createdCount)));

        return $createdCount > 0;
    }

    public function pickAuthUser()
    {
        $user = $this->testUser ? $this->testUser : User::all()->random();
        Auth::setUser($user);

        return $user;
    }

    public function needItem($modelClass, $limit, $chunk): int
    {
        if ($this->testMode) {
            return $this->sampleNeed;
        }

        $existing = $modelClass::count();
        $needItem = max($limit - $existing, 0);

        return min($needItem, $chunk);
    }

    public function getArguments()
    {
        return [
            ['content', InputArgument::OPTIONAL],
        ];
    }

    public function generateData(string $modelClass, int $need): int
    {
        $user = $this->pickAuthUser();

        /** @var Model $modelInstance */
        $modelInstance = resolve($modelClass);

        /** @var Factory $factory */
        $factory = $modelInstance::factory();

        $factory = $factory->count($need);

        if (method_exists($factory, 'setUserAndOwner')) {
            $factory = $factory->setUserAndOwner($user, $user);
        }
        if (method_exists($factory, 'seed')) {
            $factory = $factory->seed();
        }

        $factory->create([]);

        return $need;
    }

    public function downloadAssets()
    {
        $baseUrl = 'https://metafox-dev.s3.amazonaws.com/kl';
        $files   = [
            'sample/photo-1.jpeg',
            'sample/avatar-1.jpeg',
            'sample/song-1.mp3',
            'sample/video-1.mp4',
        ];

        if (!is_dir(storage_path('app/sample'))) {
            mkdir(storage_path('app/sample'));
        }
        foreach ($files as $file) {
            $this->info('Downloading ' . $file);
            file_put_contents(storage_path("app/$file"), mf_get_contents("{$baseUrl}/{$file}"));
        }
        $this->info('Copy your source asset then put to ' . storage_path('app/sample'));
    }

    public function getOptions()
    {
        return [
            ['test', 't', InputOption::VALUE_NONE, 'Run tests?'],
            ['user', 'u', InputOption::VALUE_OPTIONAL, 'User of content, etc: admin?'],
            ['count', 'c', InputOption::VALUE_OPTIONAL, 'User of content, etc: admin?'],
            ['file', 'f', InputOption::VALUE_OPTIONAL, 'User of content, etc: admin?'],
            ['mode', null, InputOption::VALUE_OPTIONAL, 'Generate type, etc: random, sequence', 'random'],
            ['multiply', 'm', InputOption::VALUE_OPTIONAL, 'Multiply value, default 10'],
        ];
    }
}
