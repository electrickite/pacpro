<?php

use Psr\Http\Message\RequestInterface as Request;

class Helpers
{
    static public function authenticationParams(Request $request)
    {
        $username = $request->getQueryParam('username');
        $key = $request->getQueryParam('api_key');
        $params = [];

        if ($key || $username) {
            $params = ['api_key' => $key, 'username' => $username];
        }

        return $params;
    }
}
