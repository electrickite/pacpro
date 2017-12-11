<?php

namespace App\Error;

class BadRequestException extends Exception
{
    protected $message = 'The request could not be understood by the server due to malformed syntax.';
}
