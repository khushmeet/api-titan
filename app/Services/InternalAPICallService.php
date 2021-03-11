<?php

namespace App\Services;

use Config\Titan;

/**
 * Make internal calls to our API end points.
 * @todo Write unit test.
 */
class InternalAPICallService
{

	/**
	 * Holds the request response.
	 * @var array
	 */
	private $response = [];

	/**
	 * Any error we may get from the request.
	 * @var string
	 */
	private $error = "";

	/**
	 * Get $this->response
	 * @return array
	 */
	public function getResponse(){
		return $this->response;
	}

	/**
	 * Get $this->error
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * Makes internal curl request.
	 * Makes a curl request to ourself, to the $url.
	 * Also attaches the internal json web token to the Authorization header.
	 * @param string $url  API end point to make the request to.
	 * @param array  $body An array of data that you want to submit as the body of the curl request.
	 */
	public function __construct(string $url = '/ping', array $body = [])
	{

		// start curl
        $curl = curl_init();

        // get titan config
        $titan = new Titan();

        // create authorization header
        $authorization = "Authorization: Bearer ".$titan->internal_json_web_token;

		// set a bunch of curl options
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => ['Content-Type: application/json' , $authorization],
            CURLOPT_URL => $_SERVER['app.baseURL'].$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        // if body is provided, we need to encode and add it to the request
        if(!empty($body)){

        	// json encode body
        	$data = json_encode($body);

        	// set the option
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        }

        // execute and get response from curl
        $this->response = curl_exec($curl);

        // if any errors occurred, put them here
        $this->error = curl_error($curl);

        // close curl
        curl_close($curl);

	}

}
