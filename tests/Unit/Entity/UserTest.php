<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Instructor;
use App\Entity\Student;
use App\Entity\User;
use App\Security\Roles;
use LogicException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function rolesProvider()
    {
        yield [
            $userType = Instructor::class,
            $contains = [Roles::INSTRUCTOR],
            $notContains = [Roles::STUDENT]
        ];

        yield [
            $userType = Student::class,
            $contains = [Roles::STUDENT],
            $notContains = [Roles::INSTRUCTOR]
        ];
    }

    /**
     * @dataProvider rolesProvider
     *
     * @param string $userType
     * @param string[] $is
     * @param string[] $isNot
     */
    public function testCorrectRoles(string $userType, array $contains, array $notContains)
    {
        $user = new User();

        if ($userType === Instructor::class) {
            $instructor = $this->createMock(Instructor::class);
            $user->setInstructor($instructor);
        }

        if ($userType === Student::class) {
            $student = $this->createMock(Student::class);
            $user->setStudent($student);
        }

        foreach ($contains as $role) {
            $this->assertContains($role, $user->getRoles());
        }

        foreach ($notContains as $role) {
            $this->assertNotContains($role, $user->getRoles());
        }
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
