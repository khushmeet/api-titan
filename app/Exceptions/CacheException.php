<?php

namespace App\Exceptions;

use App\Exceptions\BaseException;

class CacheException extends BaseException
{

    const HTTP_CODE = 500;
    const HTTP_MESSAGE = "A cache related error has occured.";

}
