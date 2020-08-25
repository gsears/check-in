<?php

/*
CourseInstanceVoter.php
Gareth Sears - 2493194S
*/

namespace App\Security\Voter;

use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use App\Entity\User;
use App\Security\Roles;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter class which controls access to Course Instance pages / information based on the user
 * and their roles.
 */
class CourseInstanceVoter extends Voter
{
    const VIEW = 'COURSEINSTANCE_VIEW';
    const EDIT = 'COURSEINSTANCE_EDIT';

    protected function supports($attribute, $subject)
    {
        // Check that the attribute that has been passed in is supported by this voter.
        // Essentially, if a non-course object is passed in, this voter will be skipped.
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof CourseInstance;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!($user instanceof User)) {
            return false;
        }

        // We know $subject is a Course instance from $this->supports
        $courseInstance = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($courseInstance, $user);
                break;
            case self::EDIT:
                return $this->canEdit($courseInstance, $user);
                break;
        }

        return false;
    }

    private function canView(CourseInstance $courseInstance, User $user)
    {
        // If they can edit, they can view
        if ($this->canEdit($courseInstance, $user)) {
            return true;
        }

        // If the user is a student, are they enrolled?
        if ($user->hasRole(Roles::STUDENT)) {
            return $courseInstance->getEnrolments()->exists(function ($key, Enrolment $enrolment) use ($user) {
                return $enrolment->getStudent() === $user->getStudent();
            });
        }

        return false;
    }

    private function canEdit(CourseInstance $courseInstance, User $user)
    {
        // Instructors who teach the course can edit
        if ($user->hasRole(Roles::INSTRUCTOR)) {
            return $courseInstance->getInstructors()->contains($user->getInstructor());
        }
    }
}
