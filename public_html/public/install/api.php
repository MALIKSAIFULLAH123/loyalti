<?php

if (function_exists('opcache_reset')) {
    opcache_reset();
}

# for security check
defined('MFOX_INSTALL') or define('MFOX_INSTALL', true);

defined('MFOX_BACKEND_ROOT') or define('MFOX_BACKEND_ROOT', dirname(dirname(__DIR__)));

$filename = MFOX_BACKEND_ROOT.'/app/SetupWizard.php';

if (!file_exists($filename)) {
    exit('Denied!');
}

include $filename;

$setupWizard = new \App\SetupWizard(MFOX_BACKEND_ROOT);

$setupWizard->execute();

exit(0);
