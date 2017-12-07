<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/verify', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'verify.xml.twig');
})->setName('verify');

$app->get('/home', function (Request $request, Response $response, array $args) {
    $provider = new Provider();
    return $this->view->render($response, 'home.xml.twig', [
        'base_url' => $request->getUri()->getBaseUrl(),
        'provider' => $provider
    ]);
})->setName('home');

$app->get('/repository', function (Request $request, Response $response, array $args) {
    $repositories = Repository::all();
    return $this->view->render($response, 'repositories.xml.twig', [
        'repositories' => $repositories
    ]);
})->setName('repositories');

$app->get('/repository/{id}', function (Request $request, Response $response, array $args) {
    $repository = new Repository($args['id']);
    return $this->view->render($response, 'repository.xml.twig', [
        'repo' => $repository
    ]);
})->setName('repository');

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
        'base_url' => $request->getUri()->getBaseUrl(),
        'packages' => $packages
    ]);
})->setName('package');

$app->get('/download/{repo}/{id}', function (Request $request, Response $response, array $args) {
    $package = new Package($args['repo'], $args['id']);
    $get_url = $request->getQueryParam('getUrl');

    if ($get_url) {
        $uri = $request->getUri();
        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->write($uri->getBaseUrl() . $uri->getPath());
    } else {
        $file = $package->transportPackagePath();

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="'.basename($file).'"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', filesize($file));

        readfile($file);
        return $response;
    }
})->setName('download');
