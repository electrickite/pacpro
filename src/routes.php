<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/verify', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'verify.xml.twig');
});
