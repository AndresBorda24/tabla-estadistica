<?php 

declare(strict_types=1);

namespace App\Http\Controllers;

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
			new Manifest(PROJECT_BASE_PATH.'/public/src/', '/src/')
		);
		$this->views->addAttribute('vite', $vite);
	}

	public function __invoke(Response $response, UserSession $user): Response 
	{
		$this->views->addAttribute('user', $user);
		return $this->views->render($response, 'index.php');
	}
}


