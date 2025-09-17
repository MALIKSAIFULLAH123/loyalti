<?php

namespace App;

use App\Setup\State;

class ProcessHelper
{
    private $_phpBinPath = null;
    private $logFile;
    private $projectRoot;

    public function __construct()
    {
        $this->projectRoot = dirname(__DIR__);

        $this->logFile = implode(
            DIRECTORY_SEPARATOR,
            [$this->projectRoot, 'storage', 'logs', sprintf('installation-%s.log', date('Y-m-d'))]
        );
    }

    public static function factory()
    {
        return new static();
    }

   public function getPhpPath()
{
    if ($this->_phpBinPath) {
        return $this->_phpBinPath;
    }

    $pathToPhp = null;

    // 1) Pehle try karo PHP_BINARY constant se (jo PHP chal rahi hai uska path)
    if (defined('PHP_BINARY') && PHP_BINARY) {
        $pathToPhp = PHP_BINARY;
    } 
    // 2) Agar wo available na ho to PHP_BINDIR se path banao
    elseif (defined('PHP_BINDIR')) {
        $pathToPhp = sprintf('%s/php.exe', PHP_BINDIR);
    } 

    // 3) Agar dono fail ho jayein to hardcoded fallback use karo
    if (!$pathToPhp || !is_executable($pathToPhp)) {
        $pathToPhp = 'F:\php8.1.13\php.exe';
    }

    // Save & return
    $this->_phpBinPath = $pathToPhp;
    return $this->_phpBinPath;
}


 public function getComposerPath()
{
    return 'composer';
}


    public function callPhp($command, $env = [], $throw = false)
    {
        $env = array_filter(array_merge(getenv(), $env), fn ($x) => !is_array($x));
        return $this->exec(sprintf('%s %s', $this->getPhpPath(), $command), $env, $throw);
    }

    public function installAllDependencies($fresh = true)
    {
        $composerLockFile = $this->projectRoot . '/composer.lock';
        if (file_exists($composerLockFile)) {
            @unlink($composerLockFile);
        }
        State::preComposerInstall();
        $this->callComposer('install --ignore-platform-req=ext-ldap --no-progress', [], true);
    }

    public function verifyComposerInstalled()
    {
        State::preDumpAutoload();
        $this->callComposer('dump-autoload -o', [], true);
    }

    public function callPackage($package, $options)
    {
        return $this->callPhp(sprintf('artisan package %s %s', $package, $options));
    }

   public function callComposer($command, $env = [], $throw = false)
{
    $env = array_merge($env, [
        'COMPOSER_MEMORY_LIMIT' => '-1',
        'COMPOSER_HOME'         => $this->projectRoot.'/storage/composer',
        'COMPOSER_ALLOW_SUPERUSER' => 1
    ]);

    $this->log('Environment: ' . json_encode($env, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // ✅ Final command
    return $this->exec(sprintf(
        '"%s" -d memory_limit=-1 %s %s',
        $this->getPhpPath(),
        $this->getComposerPath(),
        $command
    ), $env, $throw);
}

    public function exec($command, $env = [], $throw = false)
    {
        $this->log('RUN ' . $command);

        $output = [];
        $result = 0;

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $this->projectRoot, $env);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $output .= stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $this->log('OUTPUT ' . $output);

            $result = proc_close($process);

            $this->log('RESULT ' . $result);
        }

        if ($result != 0 && $throw) {
            throw new \RuntimeException(sprintf(
                'command: %s, result=%s; command output: %s',
                $command,
                $result,
                $output
            ));
        }

        return $result === 0;
    }

    private function log($message, $level = 'DEBUG')
    {
        $message = sprintf('[%s] production:%s: %s', strtoupper($level), date('Y-m-d H:i:s'), $message);
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }

    public static function downloadFile($url, $destination, $timeout = 600)
    {
        if (extension_loaded('curl')) {
            $ch = curl_init();
            $fp = fopen($destination, 'w');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        } elseif (ini_get('allow_url_fopen')) {
            $source = fopen($url, 'r');
            $fp     = fopen($destination, 'w');
            stream_copy_to_stream($source, $fp);
        } else {
            throw new \RuntimeException('Missed both "curl" extension and allow_url_fopen');
        }
    }

    public function reloadOctaneServer()
    {
        $this->callPhp('artisan octane:reload', [], false);
    }
}
