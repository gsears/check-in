<?php

/*
StudentVoter.php
Gareth Sears - 2493194S
*/

namespace App\Security\Voter;

use App\Entity\Instructor;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\InstructorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A voter class which controls access to Student specific pages / information based on the user
 * and their roles.
 */
class StudentVoter extends Voter
{
    const VIEW = "STUDENT_VIEW";
    const EDIT = "STUDENT_EDIT";

    private $instructorRepository;

    // Inject instructor repo as a dependency
    public function __construct(InstructorRepository $instructorRepository)
    {
        $this->instructorRepository = $instructorRepository;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Student;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $student = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($student, $user);
                break;
            case self::EDIT:
                return $this->canEdit($student, $user);
                break;
        }
        return false;
    }

    private function canView(Student $student, User $user)
    {
        // If they can edit, they can view
        if ($this->canEdit($student, $user)) {
            return true;
        }

        // Instructors of that student can view
        if ($instructor = $user->getInstructor()) {
            $instructors = $this->instructorRepository
                ->findByStudent($student);

            return in_array($instructor, $instructors);
        }
    }

    private function canEdit(Student $student, User $user)
    {
        // Only the student has access to edit / input on their account
        return $user->getStudent() === $student;
    }
}
