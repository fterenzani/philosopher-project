<?php

require __DIR__.'/../bootstrap.php';
$routing = require __DIR__.'/../routing.php';

$r = $container['router'];
$r->urls = $routing;

if (Socrate\Router::NOT_FOUND === $r->dispatch(ltrim(@$_GET['_url'], '/'))) {
	abort(404);
};