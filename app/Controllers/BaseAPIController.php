<?php

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Models\Kerridge\BaseKerridgeAbstract;
use App\Services\CacheService;
use App\Services\ResponseService;
use App\Traits\RateLimitTrait;
use App\Traits\TransactionLogTrait;
use App\Traits\KerridgeRequestXMLTrait;
use App\Traits\SendToKerridgeTrait;
use App\Traits\CacheTrait;
use App\Traits\ValidationTrait;

/**
 * Base API Controller.
 * This class is utilized by all of the controllers that are callable by API end points.
 * This class contains methods that allow the controllers check the rate limit, get any additional data from the request body,
 * generate Kerridge request XML, send request XML to Kerridge and get a response and convert that response into an array.
 * This class's defaultExecution() method allows the controllers to quickly utilize all of the functionality of this class.
 * @todo Refactor BaseAPIController and the controllers that extend it into their own sub folder,
 * currently unable to do this (02/05/2019), as the route seems to want to call Controllers/API::index when
 * I pass in a param to the controller. I want to be able to do this so i can version out the API.
 * @todo Write unit test.
 */
class BaseAPIController extends BaseController
{

	use CacheTrait, KerridgeRequestXMLTrait, RateLimitTrait, SendToKerridgeTrait, TransactionLogTrait,  ValidationTrait;

	/**
	 * On __construct, create new TransactionLog.
	 */
	public function __construct()
	{
		$this->newTransactionLog();
	}

    /**
     * Gets $data from Kerridge by...
     * 1. Checking rate limit.
     * 2. Getting "Kerridge Request XML".
     * 3. Sending "Kerridge Request XML" to Kerridge.
     * 4. Converting response from request to array.
     * 5. Returns array
     * @param  BaseKerridgeAbstract $model           We use $model::fetch() to generate the SimpleXMlElement.
     * @param  array                $data            Any data that we want to pass into the $model::fetch().
     * @param  array                $additional_data Any "additional data" that we want to pass into the $model::fetch().
     * @return array
     */
    public function getDataFromKerridge(BaseKerridgeAbstract $model, array $data = [], array $additional_data = [], bool $handle_errors = true)
    {

    	// 1. We should check if the rate limit has been reached
        // Will throw exception if rate limit reached
        $this->rateLimitCheck($model);

        // 2. We need to generate the xml that we want to send to Kerridge.
        // Will throw exception if unable to find "xml template file"
        $xml = $this->getKerridgeRequestXML($model, $this->transaction_log, $data, $additional_data);

        // 3. We need to send the xml to Kerridge and get a response.
        // Will throw exception if anything goes wrong, including an error response from Kerridge
        $response = $this->sendToKerridge($model, $this->transaction_log, $xml, $handle_errors);

        // 4. We need to convert the $response into an array
        $data = $model->toData($response);

        // 5. Return the nice array data
        return $data;

    }

    /**
     * Attempts to handle any "expected" exceptions that occur.
     * If the exception is not "expected", throw it up.
     * @param Exception            $e     Exception that has occurred
     * @param BaseKerridgeAbstract $model Current "model" we are using when the exception occurred
     * @throws Exception if an exception we are not expecting occurs.
     */
    public function handleException(\Exception $e, BaseKerridgeAbstract $model){

        // exceptions that we are aware may get thrown and should be handled by the $response_service->handleException()
        $expected_exceptions = [
            "App\\Exceptions\\BadGatewayException",                  // Can occur whilst attempting to connect to Kerridge
            "App\\Exceptions\\CacheException",                       // Occurs if cache purge has an issue
            "App\\Exceptions\\ClassNotFoundException",               // Occurs if unable to find a class (Should never happen in production)
            "App\\Exceptions\\FileNotFoundException",                // Occurs if unable to find "template xml" file (Should never happen in production)
            "App\\Exceptions\\GatewayTimeoutException",              // Can occur whilst attempting to connect to Kerridge
            "App\\Exceptions\\KerridgeResourceNotFoundException",    // Occurs if Kerridge response says something like "no entities found"
            "App\\Exceptions\\RateLimitException",                   // Occurs if "user" has reached their rate limit
            "App\\Exceptions\\SocketConnectionException",            // Can occur whilst attempting to connect to Kerridge
            "App\\Exceptions\\SocketCreateException",                // Can occur whilst attempting to connect to Kerridge
            "App\\Exceptions\\UnauthorizedException",                // Occur if decoding a JWT fails
            // "UncaughtException",                                  // These types of exceptions get generated within index.php, no need to catch here
            "App\\Exceptions\\ValidationException",                  // Occurs if validation fails
        ];

        // if the supplied exception is in our list of expected exceptions, let $response_service->handleException() handle it
        if(in_array(get_class($e), $expected_exceptions)){
            $response_service = new ResponseService();
            $response_service->handleException($e, $this->transaction_log, $model->url_structure);
        }

        // otherwise we need to throw the exception higher
        throw $e;

    }

    /**
     * Begins the default execution of the steps required to complete a successful transaction.
     * Majority of classes that extend BaseAPIController will end up calling this method.
     * This method performs all of the necessary steps from getting a call to returning a response.
     * @param BaseKerridgeAbstract $model
     * @param array                $params         An associative array containing any params passed to the controller.
     * @param array                $addtional_data Any additional data we want to use when we try to get data from kerridge.
     * @param CacheService         $cache_service  Instance of CacheService.
     */
    public function defaultExecution(BaseKerridgeAbstract $model, array $params = [], array $additional_data = []){

        // the logic here has a chance of throwing a bunch of exceptions out, if one occurs
        // i want to pass the exception to $this->handleException() to be handled
        try{

            // 1. Try to get $data from cache, if it exists, show data to the "user"
            $data = $this->getDataFromCache($model, $this->transaction_log);
            if($data !== null){
                $response_service = new ResponseService();
                $response_service->success($data['data'], $this->transaction_log, $data['meta']);
            }

            // 2. Try to validate any params supplied to method
            $this->runValidation($model, $params);

            // 3. Try to get $data from Kerridge
            $data = $this->getDataFromKerridge($model, $params, $additional_data);

            // 4. Save data to the cache.
            $this->saveDataToCache($model, $this->transaction_log, $data);

            // 5. Return data to the screen
            $response_service = new ResponseService();
            $response_service->success($data, $this->transaction_log);

        }catch(\Exception $e){

            $this->handleException($e, $model);

        }

    }

    /**
     * Gets data from request body.
     * @param  BaseKerridgeAbstract $model
     * @return array
     * @throws ValidationException if request doesn't have a body or there is a issue with the body.
     */
    public function getParamsFromRequest(BaseKerridgeAbstract $model)
    {

        // attempt to get submitted json
        $request = file_get_contents("php://input");
        $data = json_decode($request, true);

        // no submitted json could be found throw an exception
        if($data === null){

            // throw an exception letting the user know we cant find the expected JSON
            $message = "JSON payload could not be found.";
            $exception = new ValidationException($message);
            $this->handleException($exception, $model);

        }

        // log the request body
        $this->transaction_log->logRequestBody($data);

        // return the data
        return $data;

    }

}
