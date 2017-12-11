<?php

namespace App\Error;

class NotFoundException extends Exception
{
    protected $message = 'The requested resource was not found.';
}
