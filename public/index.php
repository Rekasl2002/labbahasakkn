<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * LOAD OUR PATHS CONFIG FILE
 *---------------------------------------------------------------
 */
$pathsPath = FCPATH . '../app/Config/Paths.php';

if (! is_file($pathsPath)) {
    $pathsPath = '/home/nagw8149/nagalighttestncollegetask.my.id/app/Config/Paths.php';
}

if (! is_file($pathsPath)) {
    header('HTTP/1.1 500 Internal Server Error.', true, 500);
    echo 'CI4 Paths.php not found. Check your $pathsPath: ' . htmlspecialchars($pathsPath, ENT_QUOTES, 'UTF-8');
    exit(1);
}

require $pathsPath;

$paths = new Paths();

/*
 *---------------------------------------------------------------
 * LOAD THE FRAMEWORK BOOTSTRAP FILE
 *---------------------------------------------------------------
 */
require rtrim($paths->systemDirectory, '\\/') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(Boot::bootWeb($paths));
