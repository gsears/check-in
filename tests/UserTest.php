<?php

namespace App\Tests;

use App\Entity\Instructor;
use App\Entity\Student;
use App\Entity\User;
use LogicException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
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
