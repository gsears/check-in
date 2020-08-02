<?php

/*
UserTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use LogicException;
use App\Entity\User;
use App\Entity\Student;
use App\Security\Roles;
use App\Entity\Instructor;
use PHPUnit\Framework\TestCase;

/**
 * Checks that student and instructor states are correct when a user is assigned as one.
 */
class UserTest extends TestCase
{
    public function testInstructorHasCorrectRoles()
    {
        $user = new User();
        $instructor = $this->createMock(Instructor::class);
        $user->setInstructor($instructor);

        $this->assertContains(Roles::INSTRUCTOR, $user->getRoles());
        $this->assertNotContains(Roles::STUDENT, $user->getRoles());
    }

    public function testStudentHasCorrectRoles()
    {
        $user = new User();
        $student = $this->createMock(Student::class);
        $user->setStudent($student);

        $this->assertContains(Roles::STUDENT, $user->getRoles());
        $this->assertNotContains(Roles::INSTRUCTOR, $user->getRoles());
    }

    public function testInstructorCannotBeStudent()
    {
        $instructor = $this->createMock(Instructor::class);
        $student = $this->createMock(Student::class);

        $user = new User();
        $user->setInstructor($instructor);

        $this->expectException(LogicException::class);
        $user->setStudent($student);
    }

    public function testStudentCannotBeInstructor()
    {
        $instructor = $this->createMock(Instructor::class);
        $student = $this->createMock(Student::class);

        $user = new User();
        $user->setStudent($student);

        $this->expectException(LogicException::class);
        $user->setInstructor($instructor);
    }
}
