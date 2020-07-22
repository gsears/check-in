<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use App\Security\Roles;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CourseInstanceVoter extends Voter
{
    // At the moment, courses are not editable.
    const VIEW = 'COURSEINSTANCE_VIEW';

    protected function supports($attribute, $subject)
    {
        // Check that the attribute that has been passed in is supported by this voter.
        // Essentially, if a non-course object is passed in, this voter will be skipped.
        return in_array($attribute, [self::VIEW])
            && $subject instanceof CourseInstance;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // We know $subject is a Course instance from $this->supports
        $courseInstance = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($courseInstance, $user);
                break;
        }

        return false;
    }

    private function canView(CourseInstance $courseInstance, User $user)
    {
        if($user->hasRole(Roles::INSTRUCTOR)) {
            // If course has instructor
            return $courseInstance->getInstructors()->contains($user->getInstructor());
        }

        if($user->hasRole(Roles::STUDENT)) {
            // If course has student
            return $courseInstance->getEnrolments()->exists(function($key, Enrolment $enrolment) use ($user) {
                return $enrolment->getStudent() === $user->getStudent();
            });
        }

        return false;
    }
}
