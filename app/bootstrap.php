<?php

/**
 * Developement evnviroment
 */
define('IS_DEV', in_array(@$_SERVER['HTTP_HOST'], ['localhost']));

/**
 * Include path
 */
set_include_path(__DIR__.'/src'. PATH_SEPARATOR . get_include_path());

/**
 * Locale setting
 */
// List of supported timezones: http://php.net/manual/en/timezones.php
date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF-8');

/**
 * Autoloading
 */
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('', __DIR__ . '/src');

/**
 * Error handler
 */
error_reporting(E_ALL);
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/tmp/logs/php_errors.txt');

if (php_sapi_name() !== 'cli') {

	$whoops = new \Whoops\Run;
	if (IS_DEV) {
		$handler = new \Whoops\Handler\PrettyPageHandler;
		$handler->setEditor('sublime');
		$whoops->pushHandler($handler);

	} else {
		$whoops->pushHandler(function($e){
			new Errors\Error(500, $e);
		});

	}
	$whoops->register();

} else {
	ini_set('display_errors', 'On');
}

/**
 * Container
 */
$container = new Pimple\Container;
$container['BASE_PATH'] = '/sos2.0/';

$container['router'] = function($c) {
	return new Socrate\Router($c);
};
$container['slugify'] = function($c) {
	return new Slugify();
};

/**
 * Interlinking functions
 */
function path($path = null) 
{
	global $container;
	return $container['BASE_PATH'] . $path;
}

function url($path = null, $schema = 'http://') 
{
	return $schema . $_SERVER['HTTP_HOST'] . path($path);
}

function url_for_home() 
{
	return url();
}

function url_for($name, $args = null) 
{
	global $container;
	return url($container['router']->getPath($name, $args));
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
	global $container;
	return $container['slugify']->slugify($text, $separator);
}

function abort()
{
	throw new \Socrate\Http404();
}

/**
 * The dispatcher
 *//*
$dispatch = function($request, $rules) use($container) 
{

	foreach ($rules as $rule) 
	{
	
		if (preg_match($rule[0], $request, $match)) 
		{

			$_GET += $match;

			if (is_callable($rule[1])) 
			{
				return $rule[1]($container);

			}

			elseif (class_exists($rule[1]))
			{
				return new $rule[1]($container);
			}

			elseif (strpos($rule[1], '@'))
			{

				$subRule = explode('@', $rule[1]);
				
				$controller = new $subRule[0]($container);
				return $controller->{$subRule[1]}();
			}

			else 
			{
				require $rule[1];
			}

		}

	}

	throw new Http404;

};*/