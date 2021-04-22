<?php

namespace Alura\Domain\Repository;

use Alura\Domain\Model\Course;
use Alura\Infrastructure\Repository\CourseRepository;
use GuzzleHttp\Client;
use PDO;
use PDOStatement;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DomCrawler\Crawler;

class PdoCourseRepository implements CourseRepository
{
    private PDO $connection;
    private Client $client;
    private Crawler $crawler;
    private string $base_uri = 'https://www.alura.com.br';
    private StreamInterface $base_html;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->client = new Client(['base_uri' => $this->base_uri, 'verify' => false]);
        $this->crawler = new Crawler(null, $this->base_uri);
        $this->base_html = $this->client->request('GET', 'cursos-online-programacao')->getBody();

        $this->createTableSql();
    }

    private function createTableSql(): void
    {
        $createTableSql = 'CREATE TABLE IF NOT EXISTS courses (id INTEGER PRIMARY KEY AUTO_INCREMENT, name TEXT, category TEXT, course_load TEXT, 
        activites TEXT, video_minutes TEXT, all_courses_conclusions TEXT, course_note TEXT);';

        $this->connection->exec($createTableSql);
    }

    public function allCourses(): array
    {
        $statement = $this->connection->query('SELECT * FROM courses');

        return $this->hydrateCourseList($statement);
    }

    /** @return Course[] */
    private function hydrateCourseList(PDOStatement $statement): array
    {
        $courseDataList = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courseDataList as $courseData) {
            $courseList[] = new Course($courseData['id'], $courseData['name'], $courseData['category'], $courseData['course_load'], 
                                       $courseData['activites'], $courseData['video_minutes'], $courseData['all_courses_conclusions'],
                                       $courseData['course_note']);
        }

        return $courseList;
    }

    public function allCategories(): array
    {
        $statement = $this->connection->query('SELECT category FROM courses');
        $categoryDataList = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categoryDataList as $categoryData) {
            $categoryList[] = $categoryData["category"];
        }

        return array_unique($categoryList);
    }

    public function coursesFromACategory(string $category): array
    {
        $sqlQuery = 'SELECT * FROM courses WHERE category = ?';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(1, $category);
        $statement->execute();

        $courseDataList = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courseDataList as $courseData) {
            $courseList[] = new Course($courseData['id'], $courseData['name'], $courseData['category'], $courseData['course_load'], 
                                       $courseData['activites'], $courseData['video_minutes'], $courseData['all_courses_conclusions'],
                                       $courseData['course_note']);
        }

        return $courseList;
    }

    public function courseDescriptionsOfId(int $id): Course
    {
        $sqlQuery = 'SELECT * FROM courses WHERE id = ?';
        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue(1, $id, PDO::PARAM_INT);
        $statement->execute();

        $courseData = $statement->fetchAll(PDO::FETCH_ASSOC)[0];

        $course = new Course($courseData['id'], $courseData['name'], $courseData['category'], $courseData['course_load'], 
                             $courseData['activites'], $courseData['video_minutes'], $courseData['all_courses_conclusions'],
                             $courseData['course_note']);

        return $course;
    }

    public function importCoursesToDB(): void
    {
        $ids = $this->getNodeIds();

        foreach ($ids as $id) {
            $categoryCrawler = $this->getSubCategotyCrawler($id);

            $categoryName = $this->getSubCategoryName($categoryCrawler);

            $courseNameList = $this->getCoursesNameFromASubCategory($categoryCrawler);

            $linkList = $this->getLinksFromASubCategory($categoryCrawler);
            
            $i = 0;
            foreach ($linkList as $link) {
                $crawler = $this->courseCrawlerFrom($link);
                $courseDetails = $this->courseDatailsFromCrawler($crawler);

                $courseName = $courseNameList[$i];
                $load = $courseDetails[0];
                $activites = $courseDetails[1];
                $videoMinutes = $courseDetails[2];
                $allCoursesConclusions = $courseDetails[3];
                $courseNote = $courseDetails[4];

                $course = new Course(null, $courseName, $categoryName, $load, $activites, $videoMinutes, $allCoursesConclusions, $courseNote);

                $this->save($course);

                $i++;
            }
        }
    }

    private function getNodeIds(): array
    {
        $this->crawler->addHtmlContent($this->base_html);

        return $this->crawler->filter('div.subcategoria.lista-subcategorias__subcategoria')->extract(['id']);
    }

    private function getSubCategotyCrawler(string $id): Crawler
    {
        return $this->crawler->filter("div#{$id}");
    }

    private function getCoursesNameFromASubCategory(Crawler $crawler): array
    {
        $courseNameDataList = $crawler->filter('span.card-curso__nome');

        foreach ($courseNameDataList as $courseNameData) {
            $courseList[] = $courseNameData->textContent;
        }

        return $courseList;
    }

    private function getLinksFromASubCategory(Crawler $crawler): array
    {
        $linkDataList = $crawler->filter('a')->links();

        foreach ($linkDataList as $linkData) {
            $linkList[] = $linkData->getUri();
        }

        return $linkList;
    }

    private function getSubCategoryName(Crawler $crawler): string
    {
        $categoryNameData = $crawler->filter('span.subcategoria__nome');

        return $categoryNameData->extract(['_text'])[0];
    }

    private function courseCrawlerFrom(string $link): Crawler
    {
        $response = $this->client->request('GET', str_replace($this->base_uri . '//', "", $link));
        $html = $response->getBody();

        $crawler = new Crawler(null, $this->base_uri);
        $crawler->addHtmlContent($html);

        return $crawler;
    }

    private function courseDatailsFromCrawler(Crawler $crawler): array
    {
        $courseDetailsDataList = $crawler->filter('div.curso-detalhes-main-content')->filter('p.courseInfo-card-wrapper-infos');

        foreach ($courseDetailsDataList as $courseDetailsData) {
            $courseDetails[] = $courseDetailsData->textContent;
        }

        return $courseDetails;
    }

    private function save(Course $course): bool
    {
        return $this->insert($course);
    }
    
    private function insert(Course $course): bool
    {
        $insertQuery = 'INSERT INTO courses (name, category, course_load, activites, video_minutes, all_courses_conclusions, course_note) 
                        VALUES (:name, :category, :course_load, :activites, :video_minutes, :all_courses_conclusions, :course_note);';

        $statement = $this->connection->prepare($insertQuery);

        $success = $statement->execute([':name' => $course->getName(), ':category' => $course->getCategory(), 
                                        ':course_load' => $course->getLoad(), ':activites' => $course->getActivites(), 
                                        ':video_minutes' => $course->getVideoMinutes(), 
                                        ':all_courses_conclusions' => $course->getAllCoursesConclusions(), 
                                        ':course_note' => $course->getCourseNote()]);
    
            
        return $success;
    }
}