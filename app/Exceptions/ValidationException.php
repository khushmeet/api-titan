<?php

namespace App\Exceptions;

use App\Exceptions\BaseException;

class ValidationException extends BaseException
{

    const HTTP_CODE = 422;
    const HTTP_MESSAGE = "Unable to process request due to validation error.";

}
