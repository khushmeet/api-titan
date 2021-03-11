<?php

// Valid PHP Version?
$minPHPVersion = '7.2';
if (phpversion() < $minPHPVersion)
{
	die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
}
unset($minPHPVersion);

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Location of the Paths config file.
// This is the line that might need to be changed, depending on your folder structure.
$pathsPath = FCPATH . '../app/Config/Paths.php';
// ^^^ Change this if you move your application folder

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

// Load our paths config file
require $pathsPath;
$paths = new Config\Paths();

// Location of the framework bootstrap file.
$app = require rtrim($paths->systemDirectory, '/ ') . '/bootstrap.php';

/*
 *---------------------------------------------------------------
 * SMALL AMOUNT OF PRE LAUNCH CUSTOMISATION
 *---------------------------------------------------------------
 */

// allow ddd() to be used throughout application if not production
if(ENVIRONMENT == 'development'){
	helper('enhanced_dump');
}

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do its thang.
 */

//////////////////////////////////////////////////////////////////////////////////////
// WRAP $app->run() TRY AND CATCH BLOCKS TO ENSURE WE CATCH EXCEPTIONS AT TOP LEVEL //
//////////////////////////////////////////////////////////////////////////////////////

use App\Services\ResponseService;
use App\Exceptions\UncaughtException;
use App\Models\TransactionLog;

// try to "run" the app
try {

	$app->run();

// catch ANY exceptions that make it this far
} catch (\Exception $e) {

	// when developing locally, it helps to just throw the exception up
	if(ENVIRONMENT == 'development'){
		throw $e;
	}

	// create new instance of transaction log
	$transaction_log = new TransactionLog();
	$transaction_log->new_();

	// create new instance of UncaughtException to be returned to the "user"
	$message = "An uncaught exception (" .get_class($e). ") was thrown. This has caused the request to fail. The developers have been informed.";
	$exception = new UncaughtException($message, null, $e);

	// get transaction guid so that it can be sent to the developers
	$transaction_guid = $transaction_log->getTransactionGUID();

	// TODO: contact devs?

	// allow ResponseService::handleException() to return some info to the "user"
	$response_service = new ResponseService();
	$response_service->handleException($exception, $transaction_log);

}

