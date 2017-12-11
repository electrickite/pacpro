<?php

namespace App\Error;

class ForbiddenException extends Exception
{
    protected $message = 'The server understood the request, but is refusing to fulfill it.';
}
