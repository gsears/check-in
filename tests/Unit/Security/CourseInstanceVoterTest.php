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

    private function createInstructorUser()
    {
        $instructor = $this->createMock(Instructor::class);

        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructor);
        $user->method('hasRole')->willReturn(Roles::INSTRUCTOR);

        return $user;
    }

    private function createStudentUser()
    {
        $student = $this->createMock(Student::class);

        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($student);
        $user->method('hasRole')->willReturn(Roles::STUDENT);

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
        // Test 1
        $courseInstance = new CourseInstance();

        yield 'anonymous cannot view' => [
            CourseInstanceVoter::VIEW,
            $courseInstance,
            null,
            Voter::ACCESS_DENIED
        ];

        // Test 2
        $user = $this->createInstructorUser();
        $courseInstance = new CourseInstance();
        $courseInstance->addInstructor($user->getInstructor());

        yield 'member instructor can view' => [
            CourseInstanceVoter::VIEW,
            $courseInstance,
            $user,
            Voter::ACCESS_GRANTED
        ];

        // Test 3
        $user = $this->createInstructorUser();
        $courseInstance = new CourseInstance();

        yield 'non member instructor cannot view' => [
            CourseInstanceVoter::VIEW,
            $courseInstance,
            $user,
            Voter::ACCESS_DENIED
        ];

        // Test 4
        $user = $this->createStudentUser();
        $enrolment = $this->createMock(Enrolment::class);
        $enrolment->method('getStudent')->willReturn($user->getStudent());
        $courseInstance = new CourseInstance();
        $courseInstance->addEnrolment($enrolment);

        yield 'member student can view' => [
            CourseInstanceVoter::VIEW,
            $courseInstance,
            $user,
            Voter::ACCESS_GRANTED
        ];

        // Test 5
        $user = $this->createStudentUser();
        $courseInstance = new CourseInstance();

        yield 'non member student cannot view' => [
            CourseInstanceVoter::VIEW,
            $courseInstance,
            $user,
            Voter::ACCESS_DENIED
        ];

        // Test 6
        $courseInstance = new CourseInstance();

        yield 'anonymous cannot edit' => [
            CourseInstanceVoter::EDIT,
            $courseInstance,
            null,
            Voter::ACCESS_DENIED
        ];

        // Test 7
        $user = $this->createStudentUser();
        $enrolment = $this->createMock(Enrolment::class);
        $enrolment->method('getStudent')->willReturn($user->getStudent());
        $courseInstance = new CourseInstance();
        $courseInstance->addEnrolment($enrolment);

        yield 'member student cannot edit' => [
            CourseInstanceVoter::EDIT,
            $courseInstance,
            $user,
            Voter::ACCESS_DENIED
        ];

        // Test 8
        $user = $this->createStudentUser();
        $courseInstance = new CourseInstance();

        yield 'non member student cannot edit' => [
            CourseInstanceVoter::EDIT,
            $courseInstance,
            $user,
            Voter::ACCESS_DENIED
        ];

        // Test 9

        $user = $this->createInstructorUser();
        $courseInstance = new CourseInstance();
        $courseInstance->addInstructor($user->getInstructor());

        yield 'member instructor can edit' => [
            CourseInstanceVoter::EDIT,
            $courseInstance,
            $user,
            Voter::ACCESS_GRANTED
        ];

        // Test 10
        $user = $this->createInstructorUser();
        $courseInstance = new CourseInstance();

        yield 'non member instructor cannot view' => [
            CourseInstanceVoter::EDIT,
            $courseInstance,
            $user,
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
