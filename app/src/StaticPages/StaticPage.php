<?php

namespace StaticPages;

use Socrate\Http404;

class StaticPage
{

	function __construct()
	{

		set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

		$path = 'views/static_pages/' . $_GET['path'] . '.php';

		if (stream_resolve_include_path($path)) {
			require $path;
		}

		throw new Http404;
	}

}