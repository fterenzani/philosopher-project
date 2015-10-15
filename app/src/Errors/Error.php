<?php

namespace Errors;

use Exception;

/**
 * Display and error page and send the HTTP status code
 */
class Error
{

	/**
	 * @statusCode	A valid HTTP status code
	 * @error 		A PHP Exception
	 */	
	function __construct($statusCode, Exception $error = null) 
	{

		$this->statusCode = $statusCode;
		$this->error = $error;

		header('x-error: 1', true, $this->statusCode);

		while (ob_get_level()) ob_end_clean();

		ob_start();

		set_include_path(get_include_path(). PATH_SEPARATOR . __DIR__);

		switch ($statusCode) {
			case '404':
			case '410':
				require 'views/errors/error.php';
				break;

			case '403':
				require 'views/errors/forbidden.php';
				break;

			case '503':
				require 'views/errors/server-maintenance.php';
			
			default:
				require 'views/errors/internal-server-error.php';
				break;
		}

	}
}