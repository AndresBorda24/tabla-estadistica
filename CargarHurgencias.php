<?php

declare(strict_types=1);

use App\Services\InfoLoaderService;
use App\Query;

require __DIR__ . "/vendor/autoload.php";

$fecha = @$_POST['fe'] ?: @$_GET['fe'] ?: date('Y-m-d');
$fechaForGema = date('m.d.y', strtotime($fecha));

$loaderService = new InfoLoaderService(new Query());
$loaderService->loadWithTriage($fechaForGema);
$loaderService->loadWithoutTriage($fechaForGema);

header("Content-Type: application/json");
echo json_encode($loaderService->getData());
