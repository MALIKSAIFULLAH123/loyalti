<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\Platform\PackageManager;
use PhpAmqpLib\Package;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FormUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'form:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update form structure';

    private function toFieldName(string $name): string
    {
        if (Str::endsWith($name, 'Field')) {
            $name = substr($name, 0, -1 * strlen('Field'));
        }

        return \Str::camel($name);
    }

    public function handle(): int
    {
        $path = $this->argument('path');

        if ('all' === $path) {
            foreach (PackageManager::getPackageNames() as $package) {
                $path =  PackageManager::getPath($package);
                $this->handleDir("$path/src/Http/Resources");
            }

            return 0;
        }

        $path = app('files')->exists($path) ? $path : base_path($path);

        if (!file_exists($path) && !is_dir($path)) {
            $path = $this->argument('path');
            $package = PackageManager::getName($path);
            $package = $package ?? PackageManager::getByAlias($path);
            $path = base_path(PackageManager::getPath($package) . '/src/Http/Resources');
        }

        if (is_dir($path)) {
            $this->handleDir($path);
        } elseif (is_file($path)) {
            $this->handleFile($path);
        }

        return 0;
    }

    public function handleDir(string $path)
    {
        $this->comment('Scanning dir ' . $path);

        foreach (glob($path . '/*Form.php') as $file) {
            $this->handleFile(realpath($file));
        }

        foreach (glob($path . '/**/*Form.php') as $file) {
            $this->handleFile(realpath($file));
        }

        foreach (glob($path . '/**/**/*Form.php') as $file) {
            $this->handleFile(realpath($file));
        }
    }

    public function handleFile(string $path): void
    {
        $this->info('Process ' . $path);
        $content = app('files')->get($path);
        $content = $this->migrateConfig($content);
        $content = $this->migrateBuilder($content);

        $this->writeToFile($path, $content);
    }

    public function migrateConfig(string $content): string
    {
        $lines = explode(PHP_EOL, $content);
        $startReg = '/^(\s+)\$this->config\(\[$/m';

        $response = [];
        $group = [];
        $name = '';
        $findEnd = null;
        $findValue = null;
        $findPair = '/^(\s+)\'([\w+-]+)\'(\s+)=>(\s+)([^,]+)(,?)$/';
        $pad = ''; // white space prefix
        $tabSpace = function (int $num): string {
            return str_pad('', 4 * $num, ' ');
        };

        $createWrap = function (
            $pad,
            &$response,
            &$group,
            &$name,
            &$findEnd,
            &$findValue
        ): void {
            $wrap = implode(PHP_EOL, $group) . ');';
            $wrap = preg_replace('/^(\s*)\],(\s*)\);$/m', $pad . ']);', $wrap);
            $this->info($wrap);
            $findEnd = null;
            $name = '';
            $group = [];
            $response[] = $wrap; // AND END LINES
            $findValue = null;
        };

        $putToGroup = function ($key, $value, $pad) use (&$group) {
            $group[] = sprintf('    %s->%s(%s)', $pad, Str::camel($key), $value);
        };
        $setToGroup = function ($key, $value, $pad) use (&$group) {
            $group[] = sprintf('    %s->set%s(%s)', $pad, Str::Studly($key), $value);
        };
        foreach ($lines as $lineNumber => $line) {
            if (preg_match($startReg, $line, $matches)) {
                $pad = $matches[1];
                $findEnd = '/^\s{' . strlen($pad) . '}\]\)(;?)$/';
                $group[] = sprintf('%s$this', $pad);
            } elseif ($findEnd && preg_match($findEnd, $line, $matches)) {
                $createWrap($pad, $response, $group, $name, $findEnd, $findValue);
            } elseif ($findEnd && $findValue) {
                $group[] = $line;
            } elseif ($findEnd) {
                if (preg_match($findPair, $line, $pairs)) {
                    $key = $pairs[2];
                    $value = $pairs[5];

                    switch ($key) {
                        case 'value':
                            $group[] = sprintf('%s    ->setValue(%s', $pad, $value);
                            if ($value === '[') {
                                $findValue = true;
                            }
                            break;
                        case 'method':
                            switch ($value) {
                                case "'POST'":
                                case 'MetaFoxForm::METHOD_POST':
                                    $putToGroup('asPost', '', $pad);
                                    break;
                                case "'PUT'":
                                case 'MetaFoxForm::METHOD_PUT':
                                    $putToGroup('asPut', '', $pad);
                                    break;
                                default:
                                    $putToGroup($key, $value, $pad);
                            }
                            break;
                        case 'repository':
                            $group[] = sprintf('    %s->set%s(%s)', $pad, Str::Studly($key), $value);
                            break;
                        default:
                            $group[] = sprintf('    %s->%s(%s)', $pad, Str::camel($key), $value);
                    }
                }
            } else {
                $response[] = $line;
            }
        }

        return implode(PHP_EOL, $response);
    }

    /**
     * Execute the console command.
     */
    public function migrateBuilder(string $content): string
    {
        $content = preg_replace('/=>(\s+)\[(\s+\[)/m', ' => [[', $content);
        $content = preg_replace('/\],\s+\[/m', '],[', $content);
        $content = preg_replace('/\],\s+\],/m', ']]', $content);
        $content = preg_replace('/([\?:])(\s+)__(p?)\(/m', '$1__$3(', $content);
        $content = preg_replace('/\]\)\);$/m', '])' . PHP_EOL . ');', $content);
        $lines = explode(PHP_EOL, $content);
        $startReg = '/^(\s+)new (\w+)\(\[$/';

        $response = [];

        $group = [];
        $name = '';
        $findEnd = null;
        $findPair = '/^(\s+)\'([\w+-]+)\'(\s+)=>(\s+)(.+)(,?)$/';
        $findYup = null;
        $yupGroup = [];
        $yupComments = [];
        $pad = ''; // white space prefix
        $findError = null;
        $yupType = 'string';
        $importedYup = false;
        $importedBuilder = false;

        $tabSpace = function (int $num): string {
            return str_pad('', 4 * $num, ' ');
        };

        $createWrap = function (
            $pad,
            &$response,
            &$group,
            &$name,
            &$findEnd,
            &$findYup,
            $yupType,
            &$yupGroup,
            &$yupComments
        ): void {
            $wrap = implode(PHP_EOL, $group);
            $wrap = str_replace('__NAME__', $name, $wrap);
            $this->info($wrap);
            $findEnd = null;
            $name = '';
            $group = [];
            $response[] = $wrap; // AND END LINES
            if (count($yupGroup)) {
                $yupWrap = implode(PHP_EOL, $yupGroup);
                $response[] = str_replace('Yup::string()', 'Yup::' . $yupType . '()', $yupWrap);
                // comment before pad
                $response[] = implode(PHP_EOL, array_map(function ($str) use ($pad) {
                    return $pad . '// ' . $str;
                }, $yupComments));
                $response[] = $pad . '    )';
            }

            $response[] = $pad . ',';
            $findYup = false;
            $yupGroup = [];
            $yupComments = [];
        };

        $putToGroup = function ($key, $value, $pad) use (&$group) {
            $group[] = sprintf('    %s->%s(%s)', $pad, Str::camel($key), $value);
        };
        $setToGroup = function ($key, $value, $pad) use (&$group) {
            $group[] = sprintf('    %s->set%s(%s)', $pad, Str::Studly($key), $value);
        };
        $markLineNumber = null;
        foreach ($lines as $lineNumber => $line) {
            if (!$markLineNumber && preg_match('/^namespace \w+/', $line)) {
                $markLineNumber = $lineNumber;
                $response[] = $line;
                $response[] = '';
                $response[] = ''; // preserver
                $response[] = ''; // preserver
                continue;
            }

            if ($line == 'use MetaFox\Form\Builder;'
                || $line == 'use MetaFox\Form\Mobile\Builder;') {
                $importedBuilder = true;
            }

            if ($line == 'use MetaFox\Yup\Yup;') {
                $importedYup = true;
            }

            if (preg_match($startReg, $line, $matches)) {
                $pad = $matches[1];
                $findEnd = '/^\s{' . strlen($pad) . '}\]\)([,]?)$/';
                $group[] = sprintf('%sBuilder::%s(__NAME__)', $pad, $this->toFieldName($matches[2]));
            } elseif ($findEnd && preg_match($findEnd, $line, $matches)) {
                $createWrap($pad, $response, $group, $name, $findEnd, $findYup, $yupType, $yupGroup, $yupComments);
            } elseif ($findEnd && $findYup) {
                if (!$findError && preg_match($findPair, $line, $pairs)) {
                    $key = $pairs[2];
                    $value = trim($pairs[5], ',');
                    switch ($key) {
                        case 'format':
                            $yupGroup[] = sprintf('%s%s->%s()', $pad, $tabSpace(3), trim($value, "'"));
                            break;
                        case 'errors':
                            $findError = true;
                            break;
                        case 'type':
                            $yupType = trim($value, "'");
                            break;
                        case 'required':
                            $value = '';
                            $yupGroup[] = sprintf('%s%s->%s(%s)', $pad, $tabSpace(3), $key, $value);
                            break;
                        default:
                            $yupGroup[] = sprintf('%s%s->%s(%s)', $pad, $tabSpace(3), $key, $value);
                    }
                }
                $yupComments[] = $line;
            } elseif ($findEnd) {
                if (preg_match($findPair, $line, $pairs)) {
                    $key = $pairs[2];
                    $value = trim($pairs[5], ',');

//                    if ($value === '[' && $name !== 'validation') {
//                        $this->error('could not parse this file because options chain!');
//                        exit(0);
//                    }

                    switch ($key) {
                        case 'validation':
                            $findError = false;
                            $yupType = 'string';
                            $yupGroup[] = sprintf('    %s->yup(', $pad);
                            $yupGroup[] = sprintf('    %s    Yup::string()', $pad);
                            $yupComments[] = $line;
                            $findYup = true;
                            break;
                        case 'returnKeyType':
                            break;
                        case 'name':
                            $name = $value;
                            break;
                        case 'editor':
                            if ($value == 'false') {
                                $putToGroup('disableEditor', '', $pad);
                            }
                            break;
                        case 'required':
                        case 'disabled':
                            if ($value === 'false') {
                                $putToGroup($key, $value, $pad);
                            } else {
                                $putToGroup($key, '', $pad);
                            }
                            break;
                        case 'size':
                        case 'margin':
                            $group[] = sprintf(
                                '    %s->%s%s()',
                                $pad,
                                $key,
                                Str::of($value)->trim("'")->studly()->toString()
                            );
                            break;
                        case 'options':
                            $group[] = sprintf('    %s->options(%s)', $pad, $value);
                            break;
                        case 'repository':
                            $group[] = sprintf('    %s->set%s(%s)', $pad, Str::Studly($key), $value);
                            break;
                        default:
                            $group[] = sprintf('    %s->%s(%s)', $pad, Str::camel($key), $value);
                    }
                }
            } else {
                $response[] = $line;
            }
        }

        // don't required yup.
        if (!strpos(implode('', $response), 'Yup::')) {
            $importedYup = true;
        }

        if ($markLineNumber) {
            if (!$importedBuilder) {
                $response[$markLineNumber + 2] = 'use MetaFox\Form\Builder;';
            }
            if (!$importedYup) {
                $response[$markLineNumber + 3] = 'use MetaFox\Yup\Yup;';
            }
        }

        return implode(PHP_EOL, $response);
    }

    /**
     * @param  string $path
     * @param  string $content
     * @return void
     */
    public function writeToFile(string $path, string $content): void
    {
        $filename = $this->option('test') ? $path . '.local.php' : $path;
        $files = app('files');
        $files->put($filename, $content);
        exec('composer phpcs ' . $filename);
    }

    public function getOptions()
    {
        return [
            ['test', 't', InputOption::VALUE_OPTIONAL, 'Test', null],
        ];
    }

    protected function getArguments()
    {
        return [
            ['path', null, InputArgument::REQUIRED, 'Path to file'],
        ];
    }
}
