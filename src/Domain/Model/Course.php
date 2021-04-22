<?php

namespace Alura\Domain\Model;

use DomainException;

class Course
{
    private ?int $id;
    private string $name;
    private string $category;
    private string $load;
    private string $activities;
    private string $videoMinutes;
    private string $allCoursesConclusions;
    private string $courseNote;

    public function __construct(?int $id, ?string $name, ?string $category, string $load, string $activities, 
                                string $videoMinutes, string $allCouresesConclusions, string $courseNote)
    {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
        $this->load = $load;
        $this->activities = $activities;
        $this->videoMinutes = $videoMinutes;
        $this->allCoursesConclusions = $allCouresesConclusions;
        $this->courseNote = $courseNote;
    }

    public function defineId(int $id): void
    {
        if (!is_null($this->id)) {
            throw new DomainException('Você só pode definir o id uma vez.');
        }

        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getLoad(): string
    {
        return $this->load;
    }

    public function getActivites(): string
    {
        return $this->activities;
    }

    public function getVideoMinutes(): string
    {
        return $this->videoMinutes;
    }

    public function getAllCoursesConclusions(): string
    {
        return $this->allCoursesConclusions;
    }

    public function getCourseNote(): string
    {
        return $this->courseNote;
    }

    public function defineName(string $name): void
    {
        $this->name = $name;
    }

    public function defineCategory(string $category): void
    {
        $this->category = $category;
    }
}
