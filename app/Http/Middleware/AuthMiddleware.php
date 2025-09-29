<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Por cuestiones de simplicidad por ahora se inicia la sesión directamente aquí.
 * @todo Crear un middleware de session separado.
 * @todo Creación del objeto UserSession en otro archivo.
 * @todo Ruta de login desde archivo de configuración
*/
class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (! isset($_SESSION['usuario'])) {
			$ruta = $request->getUri()->getPath();
			return (new EmptyResponse(302))
				->withHeader(
					'Location',
					'/login/login.php?ruta='.urlencode($ruta)
				);
		}

        $user = new \App\UserSession(
            usuario: $_SESSION['usuario'],
            id: (int) $_SESSION['id'],
            cargo: (int) $_SESSION['cargo'],
            area: $_SESSION['area'],
            areaId: (int) $_SESSION['area_id'],
            grupo: $_SESSION['grupo'],
            medicoId: $_SESSION['medico_id'] !== null ? (int) $_SESSION['medico_id'] : null,
            nombre: $_SESSION['nombre'],
        );

        $response = $handler->handle($request->withAttribute('user', $user));
        return $response;
    }
}
