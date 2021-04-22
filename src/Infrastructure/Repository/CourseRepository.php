<?php

namespace Alura\Infrastructure\Repository;

use Alura\Domain\Model\Course;

interface CourseRepository 
{
    public function importCoursesToDB(): void;
    public function allCourses(): array;
    public function allCategories(): array;
    public function coursesFromACategory(string $category): array;
    public function courseDescriptionsOfId(int $id): Course;
}