<?php

use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\View;
use Phalcon\Crypt;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Files as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as FormatterLine;
use Phalcon\Logger;
use Phalcon\Events\Manager as EventsManager;

$di = new FactoryDefault();

$di->set('config', $config);

$evtMgr = new EventsManager();
$evtMgr->enablePriorities(true);

$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
}, true);

$di->set('db', function () use ($config) {
    // db logger deleted, see git log

    $connection = new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'options' => [ \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' ],
        'charset' =>'utf8'
    ));

    return $connection;
});

Model::setup(['notNullValidations' => false]);

$di->set('modelsMetadata', function () use ($config) {
    return new MetaDataAdapter(array(
        'metaDataDir' => $config->application->cacheDir . 'metaData/'
    ));
});

$di->set('crypt', function () use ($config) {
    $crypt = new Crypt();
    $crypt->setKey($config->application->cryptSalt);
    return $crypt;
});

$di->set('dispatcher', function () use ($evtMgr) {
    $evtMgr->attach('dispatch:beforeException', new NotFoundPlugin);
#   $evtMgr->attach('dispatch:beforeDispatch',  new SecurityPlugin($di));

    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('App\Controllers');
    $dispatcher->setEventsManager($evtMgr);

    return $dispatcher;
});

$di->set('logger', function ($filename = null, $format = null) use ($config) {
    $today    = date('Y-m-d');
    $format   = $format ?: $config->get('logger')->format;
#   $filename = trim($filename ?: $config->get('logger')->filename, '\\/');
    $filename = trim($filename ?: "app-$today.log", '\\/');
    $path     = rtrim($config->get('logger')->path, '\\/') . DIRECTORY_SEPARATOR;

    $formatter = new FormatterLine($format, $config->get('logger')->date);
    $logger    = new FileLogger($path . $filename);

    $logger->setFormatter($formatter);
    $logger->setLogLevel($config->get('logger')->logLevel);

    return $logger;
});

$di->setShared('queue', function () use ($config) {
    if (isset($config->beanstalk->disabled) && $config->beanstalk->disabled) {
        return new class {
            public function put($job)
            {
                return true;
            }
        };
    }

    $queue = new Phalcon\Queue\Beanstalk(
        array(
            'host' => 'localhost',
            'port' => '11300'
        )
    );

    return $queue;
});
