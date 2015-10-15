<?php

require __DIR__.'/app/bootstrap.php';

$r = $container['router'];
$r->urls['home'] = ['#^$#', 'welcome.php'];
$r->dispatch(ltrim(@$_SERVER['PATH_INFO'], '/'));