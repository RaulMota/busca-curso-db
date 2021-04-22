<?php

use Alura\Domain\Repository\PdoCourseRepository;
use Alura\Infrastructure\Persistence\ConnectionCreatorDB;

require_once 'vendor/autoload.php';

$dsn = 'mysql:dbname=alura;host=127.0.0.1';
$user = 'root';
$password = 'rpm19980410';

$connection = ConnectionCreatorDB::createConnection($dsn, $user, $password);
$courseRepository = new PdoCourseRepository($connection);

$categories = $courseRepository->allCategories();

echo " ---------------- Categorias ---------------- " . PHP_EOL;

foreach ($categories as $category) {
    echo $category . PHP_EOL;
}

echo PHP_EOL;