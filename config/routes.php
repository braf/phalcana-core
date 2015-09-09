<?php


$router->add("public/<file>", array(
    'controller' => 'public',
    'action' => 'index',
    'file' => ".*",
));


//Set 404 paths
$router->notFound(array(
    "controller" => "error",
    "action" => "show404"
));
