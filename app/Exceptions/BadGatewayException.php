<?php

namespace App\Exceptions;

use App\Exceptions\BaseException;

class BadGatewayException extends BaseException
{

    const HTTP_CODE = 502;
    const HTTP_MESSAGE = "The server, while acting as a gateway, received an invalid response from the upstream server it accessed in attempting to fulfill the request.";

}
