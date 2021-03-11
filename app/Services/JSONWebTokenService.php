<?php

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use App\Models\CreatedJSONToken;
use \Firebase\JWT\JWT as JWT;

/**
 * Handles anything to do with JSON Web Tokens.
 * @todo Write unit test.
 */
class JSONWebTokenService
{

    /**
     * Get this applications JSON Web Token Key.
     * @return string
     */
    private function getKey()
    {

        $titan = new \Config\Titan();
        return $titan->json_web_token_key;
    }

    /**
     * Create and return JSON Web Token.
     * @param string           $user  Unique(ish) string to help identify who the JSON Web Token belongs to.
     * @param CreatedJSONToken $model Instance of CreatedJsonToken model.
     * @return string
     */
    public function createToken(string $user, CreatedJSONToken $model = null)
    {

        // get the key
        $key = $this->getKey();

        // get current time as UTC timestamp
        $date_time_utc = new \DateTime("now", new \DateTimeZone("UTC"));
        $utc_timestamp = $date_time_utc->getTimestamp();

        // get the app's base url, as this will be who the token was issued by
        helper('url');
        $url = base_url();

        // create uid, this will be used to uniquely identify the created token
        $uid = $user."-".$utc_timestamp;

        // set some values
        $data = array(
            "user" => $user,
            "uid" => $uid,
            "iss" => $url,
            "iat" => $utc_timestamp,
            "nbf" => $utc_timestamp,
        );

        // create the json web token
        $json_web_token = JWT::encode($data, $key);

        // create entity in created_json_tokens table
        $model = $model == null ? new CreatedJSONToken() : $model;
        $model->save(array_merge($data, [
            "json_web_token" => $json_web_token,
        ]));

        // return the token
        return $json_web_token;

    }

    /**
     * Get the UID From JSON Web Token.
     * @param string $json_web_token String representation of the JSON Web Token.
     * @return string
     */
    public function getUID(string $json_web_token = "")
    {

        // try to get the uid from the token
        try {

            // decode the token
            $decoded = $this->decodeToken($json_web_token);

            // return the uid
            return $decoded->uid;

        // if an exception occurs, just return empty string
        }catch(\Exception $e){

            return "";

        }

    }

    /**
     * Returns a decoded JSON Web Token.
     * If no JSON Web Token is supplied, will attempt to get the token from the request.
     * @param string $json_web_token String representation of the JSON Web Token.
     * @return object
     * @throws UnauthorizedException if an exception occurs whilst attempting to decode the JWT.
     * @throws UnauthorizedException if $this->getTokenFromRequest() method fails.
     */
    public function decodeToken(string $json_web_token = "")
    {

        // if no json web token is passed in, attempt to get it from the request
        if($json_web_token == ""){
            $json_web_token = $this->getTokenFromRequest();
        }

        // get the key
        $key = $this->getKey();

        // attempt to decode / validate the json web token,
        // if it works return the decoded values, so that we can do something
        // if it fails, catch whatever exception gets thrown and then escalate it
        try{

            // decode the token
            $decoded = JWT::decode($json_web_token, $key, array('HS256'));

        } catch (\Exception $e) {

            // the thrown exception should contain "advanced" details of what exactly happend.
            $message = "The request failed due to an thrown exception (" .get_class($e). ") whist attempting to decode the supplied JSON Web Token.";
            $exception = new UnauthorizedException($message, null, $e);
            throw $exception;

        }

        // return $decoded
        return $decoded;

    }

    /**
     * Get JSON Web Token from request.
     * @return string
     * @throws UnauthorizedException if there is no Authorization header.
     * @throws UnauthorizedException if the Authorization header is not a "Bearer token".
     */
    private function getTokenFromRequest()
    {

        // access the request
        $request = \Config\Services::request();

        // get the web token from the request
        $http_authorization = $request->getServer("HTTP_AUTHORIZATION");

        // if there is no auth, throw an exception
        if($http_authorization === null){
            $message = "The request failed due to an absent Authorization header.";
            $exception = new UnauthorizedException($message);
            throw $exception;
        }

        // first characters should be "Bearer ", if not, throw exception
        if(substr($http_authorization, 0, 7) != "Bearer "){
            $message = "The request failed due to a invalid Authorization header value. The value should be a Bearer token.";
            $exception = new UnauthorizedException($message);
            throw $exception;
        };

        // remove "Bearer " from the auth to get our json web token!
        $json_web_token = substr($http_authorization, 7, strlen($http_authorization));

        // return the json web token
        return $json_web_token;

    }

}