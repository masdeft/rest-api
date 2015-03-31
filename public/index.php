<?php

$loader = new \Phalcon\Loader();
$loader->registerDirs([
    '../app/models/'
]);
$loader->register();

$di = new \Phalcon\DI\FactoryDefault();

$di->set('db', function() {
    return new \Phalcon\Db\Adapter\Pdo\Mysql([
        "host" => "localhost",
        "username" => "rest_api",
        "password" => "rest_api",
        "dbname" => "rest_api"
    ]);
});

$app = new \Phalcon\Mvc\Micro($di);

// get list of all robots
$app->get('/api/robots', function() use ($app) {
    $phql = 'SELECT * FROM Robots ORDER BY name';
    $robots = $app->modelsManager->executeQuery($phql);

    $data = [];
    foreach ($robots as $robot) {
        $data[] = [
            'id' => $robot->id,
            'name' => $robot->name
        ];
    }

    echo json_encode($data);
});

// get robots by the name
$app->get('/api/robots/search/{name}', function($name) use ($app) {
    $phql = 'SELECT * FROM Robots WHERE name LIKE :name: ORDER BY name';
    $robots = $app->modelsManager->executeQuery($phql, [
        'name' => '%'.$name.'%'
    ]);

    $data = [];
    foreach ($robots as $robot) {
        $data[] = [
            'id' => $robot->id,
            'name' => $robot->name
        ];
    }

    echo json_encode($data);
});

// get a robot by the id
$app->get('/api/robots/id:[0-9]+', function($id) {

});

// add a new robot
$app->post('/api/robots', function() {

});

// update a robot by the id
$app->put('/api/robots/id:[0-9]+', function($id) {

});

// delete a robot by the id
$app->delete('/api/robots/id:[0-9]+', function($id) {

});

$app->notFound(function() use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
});

$app->handle();