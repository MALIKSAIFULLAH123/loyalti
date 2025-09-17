<?php

function process_setup_wizard()
{
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    // for security check
    defined('MFOX_INSTALL') or define('MFOX_INSTALL', true);

    defined('MFOX_BACKEND_ROOT') or define('MFOX_BACKEND_ROOT', dirname(__DIR__));

    $filename = MFOX_BACKEND_ROOT . '/app/SetupWizard.php';

    if (!file_exists($filename)) {
        exit('Denied!');
    }

    require_once $filename;

    $setupWizard = new \App\SetupWizard();

    $setupWizard->execute();

    exit(0);
}

// fix installation wizard
if (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    if (is_dir(__DIR__ . '/../public/install')) {
        if (strpos($requestUri, '/api/v1/install') !== false) {
            process_setup_wizard();
        } elseif (preg_match('/admincp\/app\/upgrade\/(?P<step>.+)/', $requestUri, $matches, PREG_UNMATCHED_AS_NULL)) {
            $forwardSteps = ['extract-apps', 'composer-install', 'verify-composer-installed',  'dump-autoload', 'metafox-upgrade', 'up-site', 'down-site'];
            if (in_array($matches['step'], $forwardSteps)) {
                // assign step to setp wizard.
                $_REQUEST['step'] =  $matches['step'];
                process_setup_wizard();
            }
        }
    }

    // redirect to /install when application does not exists.
    if (!file_exists(__DIR__ . '/../config/metafox.php')) {
        exit(json_encode([
            'data' => [
                'force_install'    => true,
                'installation_url' => '/install/',
            ],
        ]));
    }
}
