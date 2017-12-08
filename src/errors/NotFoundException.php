<?php

class NotFoundException extends Exception
{
    protected $message = 'The requested resource was not found.';
}
