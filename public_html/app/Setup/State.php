<?php

namespace App\Setup;

/**
 * Used for process install state.
 * WARN: This method also invoked by SetupWizard it's allows only build-int php function.
 */
class State
{
    public const DONE       = 'done';
    public const FAILED     = 'failed';
    public const PROCESSING = 'processing';
    public const STARTED    = 'started';
    public const RETRYING   = 'retrying';

    /** @var string */
    public const FILENAME = '/storage/framework/app_setup_state.local.json';

    /**
     * @var string
     */
    private $sid;

    /** @var array */
    private $data = [];

    private function __construct($sid)
    {
        $this->sid = $sid;
        $this->load();
    }

    public static function factory($sid)
    {
        return new static($sid);
    }

    public static function markStatusIsDone(string $sid)
    {
        static::markStatus($sid, static::DONE);
    }

    public static function markStatusIsProcessing(string $sid)
    {
        static::markStatus($sid, static::PROCESSING);
    }

    public static function markStatusIsFailed(string $sid)
    {
        static::markStatus($sid, static::FAILED);
    }

    public static function markStatusIsRetry(string $sid)
    {
        static::markStatus($sid, static::RETRYING);
    }

    /**
     * There no laravel install here, use this function to help set && get data.
     * @param             $array
     * @param             $name
     * @param             $default
     * @return mixed|null
     */
    private function arrGet($array, $name, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        $keys = explode('.', $name);

        while (count($keys)) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                return $default;
            }
            $array = $array[$key];
        }

        return $array;
    }

    public function load()
    {
        $json       = $this->read();
        $this->data = array_key_exists($this->sid, $json) ? $json[$this->sid] : [];
    }

    /**
     * @param             $name
     * @param             $default
     * @return mixed|null
     */
    public function get($name = null, $default = null)
    {
        $array = $this->data;
        if (!$name) {
            return $array;
        }

        $keys = explode('.', $name);

        while (count($keys)) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                return $default;
            }
            $array = $array[$key];
        }

        return $array;
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function set($name, $value)
    {
        $loc = &$this->data;
        foreach (explode('.', $name) as $step) {
            $loc = &$loc[$step];
        }
        $loc = $value;
    }

    public function save()
    {
        $all = static::read();

        $all[$this->sid] = $this->data;

        static::write($all);
    }

    /**
     * @param  array $array
     * @return void
     */
    public function update($array)
    {
        foreach ($array as $name => $value) {
            $this->set($name, $value);
        }

        $this->save();
    }

    /**
     * @return void
     */
    public function markProcessing()
    {
        $this->update([
            'status' => static::PROCESSING,
        ]);
    }

    /**
     * @return void
     */
    public function markStartIfNeeded()
    {
        if (!$this->getStatus()) {
            $this->update([
                'status'    => static::STARTED,
                'startedAt' => time(),
            ]);
        }
    }

    /**
     * @return void
     */
    public function markDone()
    {
        $this->update([
            'status' => static::DONE,
            'doneAt' => time(),
        ]);
    }

    /**
     * @return void
     */
    public function markFailed()
    {
        $this->update([
            'status'   => static::FAILED,
            'failedAt' => time(),
        ]);
    }

    /**
     * @param       $number
     * @return void
     */
    public function markRetried($number = 0)
    {
        $this->update([
            'status'    => static::RETRYING,
            'retries'   => $number,
            'retriedAt' => time(),
        ]);
    }

    /**
     * @param       $number
     * @return void
     */
    public function setAttempt($number = 0)
    {
        $this->update([
            'attemps' => $number,
        ]);
    }

    /**
     * @return bool
     */
    public function isProcessing()
    {
        return $this->getStatus() == static::PROCESSING;
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->getStatus() == static::DONE;
    }

    public function isRetrying()
    {
        return $this->getStatus() == static::RETRYING;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->getStatus() == static::FAILED;
    }

    /**
     * Get current state of status.
     * @return string|null
     */
    private function getStatus()
    {
        return $this->get('status');
    }

    public function checkInProgress($verifier = null)
    {
        if ($verifier && $verifier()) {
            $this->markDone();

            return ['success' => true, 'message' => 'status is verified'];
        }

        $status = $this->getStatus();

        switch ($status) {
            case self::DONE:
                return ['success' => true, 'debug' => 'step is done'];
            case self::FAILED:
                throw new \RuntimeException('Failed to process');
            case self::PROCESSING:
                return ['retry' => true, 'debug' => 'status is truthy'];
            default:
                return false;
        }
    }

    private static function getFilename()
    {
        return dirname(dirname(__DIR__)) . static::FILENAME;
    }

    private static function read()
    {
        $data     = null;
        $filename = static::getFilename();

        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
        }

        return (!$data || !is_array($data)) ? [] : $data;
    }

    public static function write($data)
    {
        file_put_contents(static::getFilename(), json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    public static function markStatus($sid, $status)
    {
        $json = static::read();

        if (!array_key_exists($sid, $json)) {
            $json[$sid] = [];
        }

        $json[$sid]['status'] = $status;

        static::write($json);
    }

    /**
     * Remove state.setup.json.
     * @return void
     */
    public static function reset()
    {
        $filename = static::getFilename();
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public static function preComposerInstall()
    {
        static::markStatusIsProcessing('composer-install');
    }

    /**
     * Integrate with composer.
     * @return void
     */
    public static function postComposerInstall()
    {
        static::markStatusIsDone('composer-install');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function postDumpAutoload()
    {
        static::markStatusIsDone('dump-autoload');
    }

    public static function preDumpAutoload()
    {
        static::markStatusIsProcessing('dump-autoload');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function preBuildFrontend()
    {
        static::markStatusIsProcessing('wait-frontend');
        static::markStatusIsProcessing('build-frontend');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function postBuildFrontend()
    {
        static::markStatusIsDone('wait-frontend');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function preMetafoxInstall()
    {
        static::markStatusIsProcessing('metafox-install');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function postMetafoxInstall()
    {
        static::markStatusIsDone('metafox-install');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function preMetafoxUpgrade()
    {
        static::markStatusIsProcessing('metafox-upgrade');
    }

    /**
     *Integrate with composer.
     * @return void
     */
    public static function postMetafoxUpgrade()
    {
        static::markStatusIsDone('metafox-upgrade');
    }
}
