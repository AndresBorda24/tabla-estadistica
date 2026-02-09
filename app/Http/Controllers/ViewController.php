<?php 

declare(strict_types=1);

namespace App\Http\Controllers;

// use App\Services\AnalisisIA;
use App\UserSession;
use HighLiuk\Vite\Manifest;
use HighLiuk\Vite\Vite;
use Slim\Views\PhpRenderer;
use Psr\Http\Message\ResponseInterface as Response;

class ViewController
{
	public function __construct( public readonly PhpRenderer $views) 
	{
		$vite = new Vite(
			new Manifest(PROJECT_BASE_PATH.'/public/src/', \App\Config::get('app_base_path').'/'.'src/')
		);
		$this->views->addAttribute('vite', $vite);
	}

	public function __invoke(Response $response, UserSession $user): Response 
	{
		// $analisis = new AnalisisIA(PROJECT_BASE_PATH . '/mock-data.json');
		$this->views->addAttribute('analisisIA', '');
		$this->views->addAttribute('user', $user);
		return $this->views->render($response, 'index.php');
	}
}


