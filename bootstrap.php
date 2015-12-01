<?php

/**
 * Developement evnviroment
 */
define('IS_DEV', in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']));

/**
 * Document & base path
 */
define('DOCUMENT_ROOT', __DIR__);
$base = '/';
$parts = array_slice(explode('/', explode('/public/', $_SERVER['PHP_SELF'])[0]), 1);

foreach ($parts as $part) {
    if (strpos($_SERVER['PHP_SELF'], $base . $part . '/') === 0) {
        $base .= $part . '/';
    }
}
define('BASE_PATH', $base);

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
require DOCUMENT_ROOT  . '/vendor/composer' . '/ClassLoader.php';

$loader = new \Composer\Autoload\ClassLoader();

$classMap = require DOCUMENT_ROOT  . '/vendor/composer' . '/autoload_classmap.php';
if ($classMap) {
    $loader->addClassMap($classMap);
}

$includeFiles = require DOCUMENT_ROOT  . '/vendor/composer' . '/autoload_files.php';
foreach ($includeFiles as $file) {
    Composer\Autoload\includeFile($file);
}

$loader->register(true);

/**
 * Error handler
 */
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', sprintf(DOCUMENT_ROOT . '/data/logs/php_errors-%s.txt', date('Y-m-d')));

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
                error_log(new ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']));
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

$di['router'] = function($di) {
    return new Socrate\Router($di);
};
$di['slugify'] = function($di) {
    return new Cocur\Slugify\Slugify();
};
$di['db'] = function($di) {
    return new Socrate\Pdo('sqlite:'.DOCUMENT_ROOT.'/data/db.sqlite');
};
$di['cache'] = function($di) { // @todo: find an alternative to doctrine cache
    if (IS_DEV) {
        $cache = new Doctrine\Common\Cache\ArrayCache();
    } else {
        $cache = new Doctrine\Common\Cache\PhpFileCache(DOCUMENT_ROOT.'/data/cache');    
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
function _path($path = null) 
{
    return BASE_PATH . $path;
}

function _url($path = null, $schema = 'http://') 
{
    return $schema . $_SERVER['HTTP_HOST'] . _path($path);
}

function path($name, $args = null) 
{
    global $di;
    return _path($di['router']->getPath($name, $args));
}

function url($name, $args = null, $schema = 'http://') 
{
    global $di;
    return _url($di['router']->getPath($name, $args), $schema);
}

function asset($path)
{
    $source = DOCUMENT_ROOT . '/public/' . $path;
    return _path($path) . '?' . filemtime($source);
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
 * Let's start
 */
ob_start();
