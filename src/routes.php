<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/verify', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'verify.xml.twig');
});

$app->get('/home', function (Request $request, Response $response, array $args) {
    $provider = new Provider();
    return $this->view->render($response, 'home.xml.twig', [
        'provider' => $provider
    ]);
});

$app->get('/repository', function (Request $request, Response $response, array $args) {
    $repositories = Repository::all();
    return $this->view->render($response, 'repositories.xml.twig', [
        'repositories' => $repositories
    ]);
});

$app->get('/repository/{id}', function (Request $request, Response $response, array $args) {
    $repository = new Repository($args['id']);
    return $this->view->render($response, 'repository.xml.twig', [
        'repo' => $repository
    ]);
});

$app->get('/package', function (Request $request, Response $response, array $args) {
    $repo = $request->getQueryParam('tag');
    $query = $request->getQueryParam('query');

    if ($repo) {
        $packages = Package::fromRepo($repo);
    } elseif ($query) {
        $packages = Package::search($query);
    } else {
        $packages = Package::all();
    }

    return $this->view->render($response, 'packages.xml.twig', [
        'packages' => $packages
    ]);
});
