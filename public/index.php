<?php 

declare(strict_types=1);
define('PROJECT_BASE_PATH', __DIR__.'/..');

require_once PROJECT_BASE_PATH . "/vendor/autoload.php";

$container = new \DI\Container([
	\Slim\Views\PhpRenderer::class => fn() => new \Slim\Views\PhpRenderer(__DIR__.'/../templates')
] + require PROJECT_BASE_PATH . '/config.php');

$app = \DI\Bridge\Slim\Bridge::create($container);
$app->setBasePath($container->get('base_path'));
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, false, true);

/* Rutas que requieren que la sesión esté activa */
$app->group('', function(\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/', \App\Http\Controllers\ViewController::class);
})->add(\App\Http\Middleware\AuthMiddleware::class);

/* Dejaré por ahora las solicitudes POST fuera del AuthMiddleware */
$app->post(
	'/estadisticas', 
	[
		\App\Http\Controllers\EstadisticasController::class, 
		$container->get('env') === 'prod' ? 'data' : 'devData'
	]
);

$app->run();
