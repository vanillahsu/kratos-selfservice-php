<?php declare(strict_types=1);
/**
 * Modified: prepend directory path of current file,
 * because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config\Config(
    [
        'application' => [
            'appDir'         => APP_PATH . '/',
            'controllersDir' => APP_PATH . '/controllers/',
            'modelsDir'      => APP_PATH . '/models/',
            'migrationsDir'  => APP_PATH . '/migrations/',
            'viewsDir'       => APP_PATH . '/views/',
            'pluginsDir'     => APP_PATH . '/plugins/',
            'libraryDir'     => APP_PATH . '/library/',
            'cacheDir'       => BASE_PATH . '/cache/',
            'baseUri'        => '/',
        ],
        'kratos' => [
            'api_host' => 'http://192.168.1.254:4434',
            'browser_host' => 'http://192.168.1.254:4433',
        ],
    ]
);

// vim: set et sw=4 sts=4 ts=4:
