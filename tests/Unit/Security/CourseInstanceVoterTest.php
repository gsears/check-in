<?php

/*
CourseInstanceVoterTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Entity\Student;
use App\Security\Roles;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\CourseInstance;
use PHPUnit\Framework\TestCase;
use App\Security\Voter\CourseInstanceVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Unit tests to check that security access is correctly applied for course instances.
 */
class CourseInstanceVoterTest extends TestCase
{

    public function testAnonCannotView()
    {
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::VIEW,
            $courseInstanceMock,
            null,
            Voter::ACCESS_DENIED
        );
    }

    public function testMemberInstructorCanView()
    {
        $userMock = $this->createInstructorUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, $userMock->getInstructor());

        $this->vote(
            CourseInstanceVoter::VIEW,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_GRANTED
        );
    }

    public function testNonMemberInstructorCannotView()
    {
        $userMock = $this->createInstructorUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::VIEW,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_DENIED
        );
    }

    public function testMemberStudentCanView()
    {
        $userMock = $this->createStudentUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock($userMock->getStudent(), null);

        $this->vote(
            CourseInstanceVoter::VIEW,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_GRANTED
        );
    }

    public function testNonMemberStudentCannotView()
    {
        $userMock = $this->createStudentUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::VIEW,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_DENIED
        );
    }

    public function testAnonCannotEdit()
    {
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::EDIT,
            $courseInstanceMock,
            null,
            Voter::ACCESS_DENIED
        );
    }

    public function testMemberInstructorCanEdit()
    {
        $userMock = $this->createInstructorUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, $userMock->getInstructor());

        $this->vote(
            CourseInstanceVoter::EDIT,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_GRANTED
        );
    }

    public function testNonMemberInstructorCannotEdit()
    {
        $userMock = $this->createInstructorUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::EDIT,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_DENIED
        );
    }

    public function testMemberStudentCannotEdit()
    {
        $userMock = $this->createStudentUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock($userMock->getStudent(), null);

        $this->vote(
            CourseInstanceVoter::EDIT,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_DENIED
        );
    }

    public function testNonMemberStudentCannotEdit()
    {
        $userMock = $this->createStudentUserMock();
        $courseInstanceMock = $this->createCourseInstanceMock(null, null);

        $this->vote(
            CourseInstanceVoter::EDIT,
            $courseInstanceMock,
            $userMock,
            Voter::ACCESS_DENIED
        );
    }

    private function createInstructorUserMock()
    {
        $instructor = $this->createMock(Instructor::class);

        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructor);
        $user->method('hasRole')->willReturn(Roles::INSTRUCTOR);

        return $user;
    }

    private function createStudentUserMock()
    {
        $student = $this->createMock(Student::class);

        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($student);
        $user->method('hasRole')->willReturn(Roles::STUDENT);

        return $user;
    }

    private function createCourseInstanceMock($studentMock, $instructorMock)
    {
        $courseInstanceMock = $this->createMock(CourseInstance::class);

        $getInstructorMethod = $courseInstanceMock->method('getInstructors');

        if ($instructorMock) {
            $getInstructorMethod->willReturn(new ArrayCollection([$instructorMock]));
        } else {
            $getInstructorMethod->willReturn(new ArrayCollection([]));
        }

        $getEnrolmentsMethod = $courseInstanceMock->method('getEnrolments');

        if ($studentMock) {
            $enrolment = $this->createMock(Enrolment::class);
            $enrolment->method('getStudent')->willReturn($studentMock);
            $getEnrolmentsMethod->willReturn(new ArrayCollection([$enrolment]));
        } else {
            $getEnrolmentsMethod->willReturn(new ArrayCollection([]));
        }

        return $courseInstanceMock;
    }

    private function vote(
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
