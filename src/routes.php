<?php

use App\Model\Provider;
use App\Model\Repository;
use App\Model\Package;
use App\Error\BadRequestException;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/verify', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'verify.xml.twig');
})->setName('verify');


$app->get('/home', function (Request $request, Response $response, array $args) {
    $provider = new Provider();
    return $this->view->render($response, 'home.xml.twig', [
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
    $signature = $request->getQueryParam('signature');

    if ($signature) {
        return $this->view->render($response, 'package.xml.twig', [
            'package' => Package::fromSignature($signature)
        ]);
    } elseif ($repo) {
        $packages = Package::fromRepo($repo);
    } elseif ($query) {
        $packages = Package::search($query);
    } else {
        $packages = Package::all();
    }

    return $this->view->render($response, 'packages.xml.twig', [
        'packages' => $packages
    ]);
})->setName('package');


$app->get('/package/update', function (Request $request, Response $response, array $args) {
    $signature = $request->getQueryParam('signature');
    if (empty($signature)) {
        throw new BadRequestException('Missing signature');
    }

    $package = Package::fromSignature($signature);
    $packages = $package->requiresUpdate() ? [$package->currentPackage()] : [];

    return $this->view->render($response, 'update.xml.twig', [
        'packages' => $packages
    ]);
})->setName('update');


$app->get('/download/{signature}', function (Request $request, Response $response, array $args) {
    $package = Package::fromSignature($args['signature']);
    $get_url = $request->getQueryParam('getUrl');

    if ($get_url) {
        $path = $this->router->pathFor('download', ['signature' => $package->signature], $this->view['auth']);
        $url = $request->getUri()->getBaseUrl() . $path;

        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->write($url);
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
