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
$app->get('/api/robots/{id:[0-9]+}', function($id) use ($app) {
    $phql = 'SELECT * FROM Robots WHERE id = :id:';
    $robot = $app->modelsManager->executeQuery($phql, [
        'id' => $id
    ])->getFirst();

    $response = new \Phalcon\Http\Response();

    if ($robot == false) {
        $response->setJsonContent(['status' => 'NOT FOUND']);
    } else {
        $response->setJsonContent([
            'status' => 'FOUND',
            'data' => [
                'id' => $robot->id,
                'name' => $robot->name
            ]
        ]);
    }

    return $response;
});

// add a new robot
$app->post('/api/robots', function() use ($app) {
    $robot = $app->request->getJsonRawBody();

    $phql = 'INSERT INTO Robots (name, type, year) VALUES (:name:, :type:, :year:)';

    $status = $app->modelsManager->executeQuery($phql, [
        'name' => $robot->name,
        'type' => $robot->type,
        'year' => $robot->year
    ]);

    $response = new \Phalcon\Http\Response();

    if ($status->saccess() == true) {
        $response->setStatusCode(201, "Created");
        $robot->id = $status->getModel()->id;

        $response->setJsonContent(['status' => 'OK', 'data' => $robot]);
    } else {
        $response->setStatusCode(409, "Conflict");

        $errors = [];
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(['status' => 'ERROR', 'messages' => $errors]);
    }

    return $response;
});

// update a robot by the id
$app->put('/api/robots/id:[0-9]+', function($id) use ($app) {
    $robot = $app->request->getJsonRawBody();

    $phql = 'UPDATE Robots SET name = :name:, type = :type:, year = :year: WHERE id = :id:';

    $status = $app->modelsManager->executeQuery($phql, [
        'name' => $robot->name,
        'type' => $robot->type,
        'year' => $robot->year,
        'id' => $id
    ]);

    $response = new \Phalcon\Http\Response();

    if ($status->success() == true) {
        $response->setJsonContent(['status' => 'OK']);
    } else {
        $response->setStatusCode(409, "Conflict");

        $errors = [];
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(['status' => 'ERROR', 'messages' => $errors]);
    }

    return $response;
});

// delete a robot by the id
$app->delete('/api/robots/id:[0-9]+', function($id) use ($app) {
    $phql = 'DELETE FROM Robots WHERE id = :id:';

    $status = $app->modelsManager->executeQuery($phql, ['id' => $id]);

    $response = new \Phalcon\Http\Response();

    if ($status->success() == true) {
        $response->setJsonContent(['status' => 'OK']);
    } else {
        $response->setStatusCode(409, "Conflict");

        $errors = [];
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(['status' => 'ERROR', 'messages' => $errors]);
    }
});

$app->notFound(function() use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
});

$app->handle();