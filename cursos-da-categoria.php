<?php

use Alura\Domain\Repository\PdoCourseRepository;
use Alura\Infrastructure\Persistence\ConnectionCreatorDB;

require_once 'vendor/autoload.php';

$category = readline('Enter a category: ');

$dsn = 'mysql:dbname=alura;host=127.0.0.1';
$user = 'root';
$password = 'rpm19980410';

$connection = ConnectionCreatorDB::createConnection($dsn, $user, $password);
$courseRepository = new PdoCourseRepository($connection);

$courseList = $courseRepository->coursesFromACategory($category);

echo "---------------- Cursos de {$category} ----------------" . PHP_EOL;

foreach ($courseList as $course) {
    echo "Id: {$course->getId()} -- {$course->getName()}" . PHP_EOL;
}

echo PHP_EOL;