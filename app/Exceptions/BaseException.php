<?php

namespace App\Exceptions;

/**
 * @todo Write unit test.
 */
class BaseException extends \Exception
{

    /**
     * Holds any errors that are attached to the exception.
     * @var array
     */
    private $errors = [];

    /**
     * All App\Exceptions call this, which generates the exception.
     * @param string|null    $message  Message of the exception.
     * @param int|null       $code     Code of the exception.
     * @param Exception|null $previous Any previous exception that has occured.
     */
    public function __construct(string $message = null, int $code = null, \Exception $previous = null) {

        // if message is null, use child's default message
        if($message === null){
            $message = $this::HTTP_MESSAGE;
        }

        // if code is null, use child's default code
        if($code === null){
            $code = $this::HTTP_CODE;
        }

        // create the actual exception
        parent::__construct($message, $code, $previous);

    }

    /**
     * Sets $this->errors.
     * @param array
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Gets $this->errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

}