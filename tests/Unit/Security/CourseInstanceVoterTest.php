<?php

namespace App\Tests\Unit\Security;

use Exception;

use App\Entity\Student;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\CourseInstance;
use App\Entity\User;
use App\Security\Roles;
use PHPUnit\Framework\TestCase;
use App\Security\Voter\CourseInstanceVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * TODO: Need to make this work. Failing as mock User is not being let through in voter.
 **/
class CourseInstanceVoterTest extends TestCase
{

    private function createInstructor(int $id)
    {
        $instructor = $this->createMock(Instructor::class);
        $instructor->method('getId')->willReturn($id);
        return $instructor;
    }

    private function createStudent(int $id)
    {
        $student = $this->createMock(Student::class);
        $student->method('getGuid')->willReturn($id);
        return $student;
    }

    private function createUser(int $id, $type)
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);

        switch ($type) {
            case Student::class:
                $user->method('getStudent')->willReturn($this->createStudent($id));
                $user->method('hasRole')->willReturn(Roles::STUDENT);
                break;

            case Instructor::class:
                $user->method('getInstructor')->willReturn($this->createInstructor($id));
                $user->method('hasRole')->willReturn(Roles::INSTRUCTOR);
                break;

            default:
                throw new Exception("Invalid type", 1);
                break;
        }

        return $user;
    }

    private function createCourseInstance(int $studentId, int $instructorId)
    {
        $enrolment = $this->createMock(Enrolment::class);
        $enrolment->method('getStudent')->willReturn($this->createStudent($studentId));

        $courseInstance = new CourseInstance();
        $courseInstance->addInstructor($this->createInstructor($instructorId));
        $courseInstance->addEnrolment($enrolment);

        return $courseInstance;
    }

    public function provideCases()
    {
        yield 'anonymous cannot view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            null,
            Voter::ACCESS_DENIED
        ];

        yield 'member instructor can view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            $this->createUser(1, Instructor::class),
            Voter::ACCESS_GRANTED
        ];

        yield 'non member instructor cannot view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            $this->createUser(2, Instructor::class),
            Voter::ACCESS_DENIED
        ];

        yield 'member student can view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            $this->createUser(1, Student::class),
            Voter::ACCESS_GRANTED
        ];

        yield 'non member student cannot view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            $this->createUser(2, Student::class),
            Voter::ACCESS_DENIED
        ];

        yield 'anonymous cannot edit' => [
            CourseInstanceVoter::EDIT,
            $this->createCourseInstance(1, 1),
            null,
            Voter::ACCESS_DENIED
        ];

        yield 'member student cannot edit' => [
            CourseInstanceVoter::EDIT,
            $this->createCourseInstance(1, 1),
            $this->createUser(1, Student::class),
            Voter::ACCESS_DENIED
        ];

        yield 'non member student cannot edit' => [
            CourseInstanceVoter::EDIT,
            $this->createCourseInstance(1, 1),
            $this->createUser(2, Student::class),
            Voter::ACCESS_DENIED
        ];

        yield 'instructor can edit' => [
            CourseInstanceVoter::EDIT,
            $this->createCourseInstance(1, 1),
            $this->createUser(1, Instructor::class),
            Voter::ACCESS_GRANTED
        ];

        yield 'non member instructor cannot view' => [
            CourseInstanceVoter::VIEW,
            $this->createCourseInstance(1, 1),
            $this->createUser(2, Instructor::class),
            Voter::ACCESS_DENIED
        ];
    }

    /**
     * @dataProvider provideCases
     */
    public function testVote(
        string $attribute,
        CourseInstance $courseInstance,
        ?User $user,
        $expectedVote
    ) {
        $voter = new CourseInstanceVoter();

        $token = new AnonymousToken('secret', 'anonymous');
        if ($user) {
            $token = new UsernamePasswordToken(
                $user,
                'password',
                'memory'
            );
        }

        $this->assertSame(
            $expectedVote,
            $voter->vote($token, $courseInstance, [$attribute])
        );
    }
}
