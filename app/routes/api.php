<?php

use Doctrine\DBAL\DriverManager as DriverManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

include __DIR__ . '/../middlewares/jsonBodyParser.php';
include __DIR__ . '/../middlewares/apiKeyVerifier.php';
include __DIR__ . '/../middlewares/dataValidator.php';

$app->get('/', function(Request $request, Response $response) {
    $response_str = json_encode(['message' => 'Welcome to our Cricket API']);
    $response->getBody()->write($response_str);
    return $response->withHeader('Content-Type', 'application/json');
});

/*
 Route to get all players
*/

$app->get('/players/', function(Request $request, Response $response) {
    
    $queryBuilder = $this->get('DB')->getQueryBuilder();

    $queryBuilder
        ->select('Id', 'Name', 'Team', 'Category')
        ->from('Players')
    ;
    
    $results = $queryBuilder->executeQuery()->fetchAllAssociative();

    $response->getBody()->write(json_encode($results));
    return $response
            ->withHeader('content-type', 'application/json');

});

/*
 Route to get a single player
*/

$app->get('/player/{id}', function(Request $request, Response $response, array $args) {
    
    $queryBuilder = $this->get('DB')->getQueryBuilder();

    $queryBuilder
        ->select('Id', 'Name', 'Team', 'Category')
        ->from('Players')
        ->where('Id = ?')
        ->setParameter(0, $args['id'])
    ;
    
    $results = $queryBuilder->executeQuery()->fetchAssociative();

    $response->getBody()->write(json_encode($results));
    return $response
            ->withHeader('content-type', 'application/json');

});

/*
 Route to add a new player
*/

$app->post('/player/add', function(Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();

    $queryBuilder = $this->get('DB')->getQueryBuilder();
    $queryBuilder
        ->insert('Players')
        ->setValue('Name', '?')
        ->setValue('Team', '?')
        ->setValue('Category', '?')
        ->setParameter(0, $parsedBody['Name'])
        ->setParameter(1, $parsedBody['Team'])
        ->setParameter(2, $parsedBody['Category'])
    ;

    $results = $queryBuilder->executeQuery()->fetchAssociative();

    $response->getBody()->write(json_encode($results));
    return $response->withHeader('content-type', 'application/json');
})->add($dataValidator)
  ->add($apiKeyVerifier);

/*
Route to update a player
*/

$app->put('/player/{id}', function(Request $request, Response $response, array $args) {
    
    $parsedBody = $request->getParsedBody();
    $queryBuilder = $this->get('DB')->getQueryBuilder();

    $queryBuilder
        ->update('Players')
        ->set('Name', '?')
        ->set('Team', '?')
        ->set('Category', '?')
        ->where('Id = ?')
        ->setParameter(0, $parsedBody['Name'])
        ->setParameter(1, $parsedBody['Team'])
        ->setParameter(2, $parsedBody['Category'])
        ->setParameter(3, $args['id'])
    ;

    $result = $queryBuilder->executeStatement();

    $response->getBody()->write(json_encode($result));
    return $response->withHeader('content-type', 'application/json');
    
  })->add($jsonBodyParser)
    ->add($dataValidator)
    ->add($apiKeyVerifier);

/*
    Route to delete a player
*/

$app->delete('/player/{id}', function (Request $request, Response $response, array $args) {

        $queryBuilder = $this->get('DB')->getQueryBuilder();

        $queryBuilder
            ->delete('Players')
            ->where('Id = ?')
            ->setParameter(0, $args['id'])
        ;

        $result = $queryBuilder->executeStatement();

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('content-type', 'application/json');

    })->add($jsonBodyParser)
      ->add($apiKeyVerifier);

