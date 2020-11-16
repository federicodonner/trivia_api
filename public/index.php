<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/auxiliares/funciones.php';


$app = new \Slim\App;
require '../src/middleware/authentication.php';

// Customer routes
require '../src/routes/juego.php';
// require '../src/routes/oauth.php';
require '../src/routes/cors.php';


$app->run();

// echo('hla');
