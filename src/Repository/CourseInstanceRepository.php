<?php

/*
CourseInstanceRepository.php
Gareth Sears - 2493194S
*/

namespace App\Repository;;

use App\Entity\Course;
use App\Entity\CourseInstance;
use App\Provider\DateTimeProvider;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourseInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseInstance[]    findAll()
 * @method CourseInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseInstance::class);
    }

    public function findAllActive(?DateTime $currentDate = null)
    {
        if (!$currentDate) {
            $currentDate = (new DateTimeProvider)->getCurrentDateTime();
        }

        return $this->createQueryBuilder('ci')
            ->andWhere('ci.startDate <= :currentDate')
            ->andWhere('ci.endDate >= :currentDate')
            ->setParameter('currentDate', $currentDate)
            ->orderBy('ci.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all CourseInstance objects which match the student.
     * @return CourseInstance[] Returns an array of CourseInstance objects
     */

    public function findByStudent($student)
    {
        return $this->createQueryBuilder('c')
            ->join('c.enrolments', 'e')
            ->andWhere('e.student = :student')
            ->setParameter('student', $student)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all CourseInstance objects which match the instructor.
     * @return CourseInstance[] Returns an array of CourseInstance objects
     */

    public function findByInstructor($instructor)
    {
        return $this->createQueryBuilder('c')
            ->join('c.instructors', 'i')
            ->andWhere('i = :instructor')
            ->setParameter('instructor', $instructor)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a course instance by its course and its instance index number
     *
     * @param integer $instanceIndex
     * @param integer $courseCode
     * @return CourseInstance
     */
    public function findByIndexAndCourseCode(int $instanceIndex, string $courseCode): ?CourseInstance
    {
        return $this->createQueryBuilder('ci')
            ->andWhere('ci.indexInCourse = :instanceIndex')
            ->setParameter('instanceIndex', $instanceIndex)
            ->andWhere('ci.course = :courseId')
            ->setParameter('courseId', $courseCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the next available index for a course instance in a course.
     *
     * @param Course $course
     * @return integer
     */
    public function getNextIndexInCourse(Course $course): int
    {
        $max = $this->createQueryBuilder('ci')
            ->select('MAX(ci.indexInCourse)')
            ->where('ci.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult();

        return $max + 1;
    }

    /**
     * Check if a course instance exists in a course with the matching index.
     *
     * @param integer $index
     * @param Course $course
     * @return boolean
     */
    public function indexExistsInCourse(int $index, Course $course): bool
    {
        return $index < $this->getNextIndexInCourse($course);
    }
}
