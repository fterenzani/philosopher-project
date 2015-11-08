<?php

require __DIR__.'/../bootstrap.php';

$r = $container['router'];
$r->urls['home'] = ['#^$#', 'welcome.php'];

if (Socrate\Router::NOT_FOUND === $r->dispatch(ltrim(@$_GET['_url'], '/'))) {
	abort(404);
};