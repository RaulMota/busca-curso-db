<?php

use Alura\Domain\Repository\PdoCourseRepository;
use Alura\Infrastructure\Persistence\ConnectionCreatorDB;

require_once 'vendor/autoload.php';

$id = readline('Enter a course Id: ');

$dsn = 'mysql:dbname=alura;host=127.0.0.1';
$user = 'root';
$password = 'rpm19980410';

$connection = ConnectionCreatorDB::createConnection($dsn, $user, $password);
$courseRepository = new PdoCourseRepository($connection);

$course = $courseRepository->courseDescriptionsOfId($id);
echo "---------------- {$course->getName()} ----------------" . PHP_EOL;

echo "Id: {$course->getId()}" . PHP_EOL;
echo "Nome: {$course->getName()}" . PHP_EOL;
echo "Categoria: {$course->getCategory()}" . PHP_EOL;
echo "Carga Horária: {$course->getLoad()}" . PHP_EOL;
echo "Número de atividade: {$course->getActivites()}" . PHP_EOL;
echo "Minutos de vídeo: {$course->getVideoMinutes()}" . PHP_EOL;
echo "Alunos nesse curso: {$course->getAllCoursesConclusions()}" . PHP_EOL;
echo "Nota do curso: {$course->getcourseNote()}" . PHP_EOL;

echo PHP_EOL;