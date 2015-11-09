<?php

require __DIR__.'/../app/bootstrap.php';
$routing = require __DIR__.'/../app/routing.php';

$r = $container['router'];
$r->urls = $routing;

if (Socrate\Router::NOT_FOUND === $r->dispatch(ltrim(@$_GET['_url'], '/'))) {
	abort(404);
};