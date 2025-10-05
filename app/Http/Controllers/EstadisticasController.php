<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InfoLoaderService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EstadisticasController
{
	public function data(
		Request $request,
		InfoLoaderService $loaderService
	): Response {
		$fechaGet  = @$request->getQueryParams()['fe'];
		$fechaPost = @$request->getParsedBody()['fe'];

		$fecha = $fechaPost ?: $fechaGet ?: date('Y-m-d');
		$fechaForGema = date('m.d.y', strtotime($fecha));

		$loaderService->loadWithTriage($fechaForGema);
		$loaderService->loadWithoutTriage($fechaForGema);

		return new JsonResponse($loaderService->getData());
	}	

	/**
	 * Este método solamente debe ser utilizado en entornos de desarrollo.
	 * Está pensado para un cargue de información fija para evitar consultas y 
	 * agilizar el desarrollo.
	 */ 
	public function devData(): Response {
		return new JsonResponse(json_decode(file_get_contents(
			PROJECT_BASE_PATH . '/mock-data.json'
		), true));
	}	
}