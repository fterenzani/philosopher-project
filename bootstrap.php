<?php

/**
 * Developement evnviroment
 */
define('IS_DEV', in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']));

/**
 * Include path
 */
set_include_path(__DIR__. PATH_SEPARATOR . get_include_path());

/**
 * Locale setting
 */
// List of supported timezones: http://php.net/manual/en/timezones.php
date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF-8');

/**
 * Autoloading
 */

require __DIR__  . '/vendor/composer' . '/ClassLoader.php';

$loader = new \Composer\Autoload\ClassLoader();

$classMap = require __DIR__  . '/vendor/composer' . '/autoload_classmap.php';
if ($classMap) {
    $loader->addClassMap($classMap);
}

$includeFiles = require __DIR__  . '/vendor/composer' . '/autoload_files.php';
foreach ($includeFiles as $file) {
    Composer\Autoload\includeFile($file);
}

$loader->register(true);

$loader->add('', __DIR__);

/**
 * Error handler
 */
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', sprintf(__DIR__ . '/data/logs/php_errors-%s.txt', date('Y-m-d')));

if (php_sapi_name() !== 'cli') {

    if (IS_DEV) {
        $whoops = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->setEditor('sublime');
        $whoops->pushHandler($handler);
        $whoops->register();

    } else {

        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (error_reporting() & $errno) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        });

        set_exception_handler(function($e) {
            error_log($e);
            abort(500);
        });

        register_shutdown_function(function() {
            $e = error_get_last();
            if ($e['type'] === E_ERROR) {
                error_log($e);
                abort(500);
            }
        });

    }

}

/**
 * Dependency Injection Container
 * http://pimple.sensiolabs.org/
 */
$di = new Pimple\Container;

if (IS_DEV) {
    $di['BASE_PATH'] = '/philosopher-project/';
} else {
    $di['BASE_PATH'] = '/';
}

$di['router'] = function($di) {
    return new Socrate\Router($di);
};
$di['slugify'] = function($di) {
    return new Cocur\Slugify\Slugify();
};
$di['db'] = function($di) {
    return new Socrate\Pdo('sqlite:'.__DIR__.'/data/db.sqlite');
};
$di['cache'] = function($di) {
    if (IS_DEV) {
        $cache = new Doctrine\Common\Cache\ArrayCache();
    } else {
        $cache = new Doctrine\Common\Cache\PhpFileCache(__DIR__.'/data/cache');    
    }
    $cache->setNamespace(@$_SERVER['HTTP_HOST']);
    return $cache;
};
$di['mailer'] = function($di) {
    if (IS_DEV) {
        $transport = Swift_NullTransport::newInstance();
    } else {
        $transport = Swift_MailTransport::newInstance();    
    }
    return Swift_Mailer::newInstance($transport);
};

/**
 * Interlinking functions
 */
function path($path = null) 
{
    global $di;
    return $di['BASE_PATH'] . $path;
}

function url($path = null, $schema = 'http://') 
{
    return $schema . $_SERVER['HTTP_HOST'] . path($path);
}

function url_for_home() 
{
    return url();
}

function path_for($name, $args = null) 
{
    global $di;
    return path($di['router']->getPath($name, $args));
}

function url_for($name, $args = null, $schema = 'http://') 
{
    global $di;
    return url($di['router']->getPath($name, $args), $schema);
}

/**
 * Utility functions
 */
function escape($input) 
{
    return htmlspecialchars($input, ENT_QUOTES|ENT_HTML5, 'UTF-8');
}

function slugify($text, $separator = '-') 
{
    global $di;
    return $di['slugify']->slugify($text, $separator);
}

function abort($statusCode = 404, Exception $exception = null)
{
    global $di;
    return new Socrate\ErrorPage($di, $statusCode, $exception);
}

function isAjax()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';     
}

/**
 * Caching helpers
 */
function cacheStart($id, $lifeTime = 0)
{
    global $di;

    $cache = $di['cache'];
    
    if ($cache->contains($id)) {
        echo $cache->fetch($id);
        return false;
    }

    ob_start(function($content) use ($cache, $id, $lifeTime){
        $cache->save($id, $content, $lifeTime);
        return $content;
    });

    return true;
}

function cacheStop() 
{
    echo ob_get_clean();
}

/**
 * Let's start
 */
ob_start();
