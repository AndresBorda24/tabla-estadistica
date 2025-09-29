<?php 

declare(strict_types=1);

namespace App\Http\Controllers;

use App\UserSession;
use Slim\Views\PhpRenderer;
use Psr\Http\Message\ResponseInterface as Response;

class ViewController
{
	public function __construct(
		public readonly PhpRenderer $views
	) {}

	public function __invoke(Response $response, UserSession $user): Response 
	{
		return $this->views->render($response, 'index.php', [
			'user' => $user
		]);
	}
}


