<?php declare(strict_types=1);

require_once BASE_PATH . '/../vendor/autoload.php';

use Phalcon\Html\Escaper;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Http\Response\Cookies;
use Phalcon\Logger\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Mvc\Url as UrlResolver;

/**
 * Shared configuration service
 */
$di->setShared(
    'config',
    function () {
        return include APP_PATH . "/config/config.php";
    }
);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared(
    'url',
    function () {
        $config = $this->getConfig();

        $url = new UrlResolver();
        $url->setBaseUri($config->application->baseUri);

        return $url;
    }
);

/**
 * Setting up the view component
 */
$di->setShared(
    'view',
    function () {
        $config = $this->getConfig();

        $view = new View();
        $view->setDI($this);
        $view->setViewsDir($config->application->viewsDir);

        $view->registerEngines(
            [
                '.volt' => function ($view) {
                    $config = $this->getConfig();
    
                    $volt = new VoltEngine($view, $this);

                    $volt->setOptions(
                        [
                            'path' => $config->application->cacheDir,
                            'separator' => '_'
                        ]
                    );

                    return $volt;
                },
                '.phtml' => PhpEngine::class
            ]
        );

        return $view;
    }
);

/**
 * If the configuration specify the use of metadata adapter use it
 * or use memory otherwise
 */
$di->setShared(
    'modelsMetadata',
    function () {
        return new MetaDataAdapter();
    }
);

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set(
    'flash',
    function () {
        $escaper = new Escaper();
        $flash = new Flash($escaper);
        $flash->setImplicitFlush(false);
        $flash->setCssClasses(
            [
                'error'   => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice'  => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ]
        );

        return $flash;
    }
);

/**
 * Start the session the first time some component request the session service
 */
$di->setShared(
    'session',
    function () {
        $session = new SessionManager();
        $files = new SessionAdapter(
            [
                'savePath' => sys_get_temp_dir(),
            ]
        );
        $session->setAdapter($files);
        $session->start();

        return $session;
    }
);

$di->setShared(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);

        return $cookies;
    }
);

$di->setShared(
    'logger',
    function () {
        $adapter = new Stream('/tmp/main.log');
        $logger = new Logger(
            'messages',
            [
                'main' => $adapter
            ]
        );

        return $logger;
    }
);

// vim: set et sw=4 sts=4 ts=4:
