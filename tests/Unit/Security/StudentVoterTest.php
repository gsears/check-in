<?php

/*
StudentVoterTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Entity\Student;
use App\Security\Roles;
use App\Entity\Enrolment;
use App\Entity\Instructor;
use App\Entity\CourseInstance;
use App\Repository\InstructorRepository;
use PHPUnit\Framework\TestCase;
use App\Security\Voter\CourseInstanceVoter;
use App\Security\Voter\StudentVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Unit tests to check that security access is correctly applied for student access.
 */
class StudentVoterTest extends TestCase
{
    public function testAnonCannotView()
    {
        $studentMock = $this->createMock(Student::class);
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::VIEW,
            $studentMock,
            $instructorRepositoryMock,
            null,
            Voter::ACCESS_DENIED
        );
    }

    public function testSameStudentCanView()
    {
        $studentMock = $this->createMock(Student::class);
        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($studentMock);
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::VIEW,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_GRANTED
        );
    }

    public function testOtherStudentCannotView()
    {
        $studentMock = $this->createMock(Student::class);
        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($this->createMock(Student::class));
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::VIEW,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_DENIED
        );
    }

    public function testStudentInstructorCanView()
    {
        $studentMock = $this->createMock(Student::class);

        $instructorMock = $this->createMock(Instructor::class);
        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructorMock);

        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);
        $instructorRepositoryMock->method('findByStudent')->willReturn([$instructorMock]);

        $this->vote(
            StudentVoter::VIEW,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_GRANTED
        );
    }

    public function testOtherInstructorCannotView()
    {
        $studentMock = $this->createMock(Student::class);

        $instructorMock = $this->createMock(Instructor::class);
        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructorMock);

        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);
        $instructorRepositoryMock->method('findByStudent')->willReturn([]);

        $this->vote(
            StudentVoter::VIEW,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_DENIED
        );
    }

    public function testAnonCannotEdit()
    {
        $studentMock = $this->createMock(Student::class);
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::EDIT,
            $studentMock,
            $instructorRepositoryMock,
            null,
            Voter::ACCESS_DENIED
        );
    }

    public function testSameStudentCanEdit()
    {
        $studentMock = $this->createMock(Student::class);
        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($studentMock);
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::EDIT,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_GRANTED
        );
    }

    public function testOtherStudentCannotEdit()
    {
        $studentMock = $this->createMock(Student::class);
        $user = $this->createMock(User::class);
        $user->method('getStudent')->willReturn($this->createMock(Student::class));
        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);

        $this->vote(
            StudentVoter::EDIT,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_DENIED
        );
    }

    public function testStudentInstructorCannotEdit()
    {
        $studentMock = $this->createMock(Student::class);

        $instructorMock = $this->createMock(Instructor::class);
        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructorMock);

        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);
        $instructorRepositoryMock->method('findByStudent')->willReturn([$instructorMock]);

        $this->vote(
            StudentVoter::EDIT,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_DENIED
        );
    }

    public function testOtherInstructorCannotEdit()
    {
        $studentMock = $this->createMock(Student::class);

        $instructorMock = $this->createMock(Instructor::class);
        $user = $this->createMock(User::class);
        $user->method('getInstructor')->willReturn($instructorMock);

        $instructorRepositoryMock = $this->createMock(InstructorRepository::class);
        $instructorRepositoryMock->method('findByStudent')->willReturn([]);

        $this->vote(
            StudentVoter::EDIT,
            $studentMock,
            $instructorRepositoryMock,
            $user,
            Voter::ACCESS_DENIED
        );
    }

    private function vote(
        string $attribute,
        Student $studentMock,
        InstructorRepository $instructorRepositoryMock,
        ?User $user,
        $expectedVote
    ) {

        $voter = new StudentVoter($instructorRepositoryMock);

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
            $voter->vote($token, $studentMock, [$attribute])
        );
    }
}
