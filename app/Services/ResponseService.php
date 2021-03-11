<?php

namespace App\Services;

use App\Models\TransactionLog;

/**
 * This class handles any sort of response we want to send to the user.
 */
class ResponseService
{

    /**
     * Prepare $data for successful response to user.
     * Creates a transaction log if none is supplied.
     * Ensures $data is correctly formated before calling $this->response().
     * @param  array               $data            [description]
     * @param  TransactionLog|null $transaction_log Instance of TransactionLog or null.
     * @param  array               $cache           [description]
     */
    public function success(array $data = [], TransactionLog $transaction_log = null, array $cache = [])
    {

        // if no transaction log has been passed, create a new instance
        if($transaction_log === null){
            $transaction_log = new TransactionLog();
            $transaction_log->new_();
        }

        // create array to pass to $this->response
        $array = [
            'state' => 'success',
            'transaction_guid' => $transaction_log->getTransactionGUID(),
            'cache' => $cache,
            'data' => $data,
        ];

        // if $cache is empty, remove it from $array
        if(empty($cache)){
            unset($array['cache']);
        }

        // if $data is empty, remove it from $array
        if(empty($data)){
            unset($array['data']);
        }

        // create and send response
        $this->response($array, 200, $transaction_log);

    }

    /**
     * Handle an exception.
     * Logs the exception in the transaction log table before calling $this->response().
     * @param  Exception           $e               Instance of any class that extends \Exception.
     * @param  TransactionLog|null $transaction_log Instance of TransactionLog or null.
     * @param  string              $url_structure   URL structure of the API end point that was called.
     */
    public function handleException(\Exception $e, TransactionLog $transaction_log = null, $url_structure = "")
    {

        // if no transaction log has been passed, create a new instance
        if($transaction_log === null){
            $transaction_log = new TransactionLog();
            $transaction_log->new_();
        }

        // log the exception
        $transaction_log->logException($e);

        // convert data into an array
        $data = [
            'state' => 'error',
            'transaction_guid' => $transaction_log->getTransactionGUID(),
            'url' => '/'.$url_structure,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            // 'errors' => [],
        ];

        // if exception has the getErrors method, then add its errors to $data
        if(method_exists($e, "getErrors")){

            // get errors
            $errors = $e->getErrors();

            // if any errors exist, add it to $data
            if(!empty($errors)){
                $data["errors"] = $e->getErrors();
            }

        }

        // if $url_strucutre is not passed in, remove it form $data
        if($url_structure == ""){
            unset($data['url']);
        }

        // issue the response
        $this->response($data, $e->getCode(), $transaction_log);

    }

    /**
     * Displays a response for the user.
     * Logs the supplied data to the transaction log before marking the transaction as complete.
     * Then uses CodeIgnitiers \Config\Services::response() to generate and display a response for the user.
     * @param  array          $data            Array of data to be displayed to the user.
     * @param  int            $code            HTTP status code.
     * @param  TransactionLog $transaction_log Instance of TransactionLog.
     */
    private function response(array $data, int $code, TransactionLog $transaction_log)
    {

        // log the response that is about to be shown to the user
        $transaction_log->logResponseJSON($data);

        // use CI in built response
        $response = \Config\Services::response();
        $response->setStatusCode($code);
        $response->setJSON($data);

        // do this as late as possible, as we want it to be the
        // last thing that happens before the code stops executing
        $transaction_log->transactionComplete();

        // send response to screen
        $response->send();

        // we don't want the code to carry on any more
        exit();

    }

}